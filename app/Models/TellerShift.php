<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class TellerShift extends Model
{
    use HasFactory;

    protected $table = 'teller_shifts';

    protected $fillable = [
        'user_id',
        'shared_user_ids',
        'opening_balance',
        'closing_balance',
        'system_balance',
        'difference',
        'notes',
        'status',
        'approved_by',
        'closed_by',
        'opened_at',
        'closed_at',
        'approved_at',
    ];

    protected $casts = [
        'shared_user_ids' => 'array',
        'opening_balance' => 'integer',
        'closing_balance' => 'integer',
        'system_balance'  => 'integer',
        'difference'      => 'integer',
        'opened_at'       => 'datetime',
        'closed_at'       => 'datetime',
        'approved_at'     => 'datetime',
    ];

    /**
     * Relasi ke model User (Teller pertama yang membuka shift / pemilik utama).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke model User (Teller yang melakukan tutup shift fisik).
     */
    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Relasi ke model User (Admin yang menyetujui shift).
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * [UJIAN / PENGUJI - MODEL 1]: Mengambil Shift Aktif Bersama (getActiveOpenShift)
     * Jika sudah ada shift aktif berstatus 'Open', seluruh loket otomatis berbagi shift & laci kas ini.
     */
    public static function getActiveOpenShift(): ?self
    {
        return self::where('status', 'Open')->latest()->first();
    }

    /**
     * [UJIAN / PENGUJI - MODEL 2]: Multi-Teller Auto-Attachment ke Laci Bersama (attachTeller)
     * Mendaftarkan Teller 2 ke dalam shared_user_ids tanpa perlu modal ganda atau tutup laci rekan.
     */
    public function attachTeller(int $userId): void
    {
        if ($this->user_id === $userId) {
            return;
        }

        // Cek secara aman jika kolom shared_user_ids sudah ada di database hosting/lokal
        if (!\Illuminate\Support\Facades\Schema::hasColumn('teller_shifts', 'shared_user_ids')) {
            return;
        }

        $shared = is_array($this->shared_user_ids) ? $this->shared_user_ids : [];
        if (!in_array($userId, $shared)) {
            $shared[] = $userId;
            $this->shared_user_ids = array_values(array_unique($shared));
            
            try {
                $this->save();
            } catch (\Throwable $e) {
                // Jangan biarkan dirty attribute jika gagal
                unset($this->attributes['shared_user_ids']);
            }
        }
    }

    /**
     * Mengambil daftar ID seluruh petugas teller yang bertugas dalam shift bersama ini.
     */
    public function getParticipatingUserIds(): array
    {
        $ids = [$this->user_id];
        if (\Illuminate\Support\Facades\Schema::hasColumn('teller_shifts', 'shared_user_ids') && !empty($this->shared_user_ids)) {
            $shared = is_array($this->shared_user_ids) ? $this->shared_user_ids : json_decode((string)$this->shared_user_ids, true);
            if (is_array($shared)) {
                $ids = array_merge($ids, $shared);
            }
        }
        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Mengambil koleksi model User dari seluruh teller yang berpartisipasi.
     */
    public function getParticipatingTellers(): Collection
    {
        $ids = $this->getParticipatingUserIds();
        return User::whereIn('id', $ids)->get();
    }

    /**
     * Menghitung total transaksi setoran tunai sejak shift dibuka.
     * Jika $specificUserId diisi, hanya menghitung kontribusi teller tersebut.
     */
    public function calculateTotalDeposits(?int $specificUserId = null): float
    {
        $userIds = $specificUserId ? [$specificUserId] : $this->getParticipatingUserIds();
        $shiftStart = $this->opened_at ?? $this->created_at;

        return (float) Transaction::whereIn('user_id', $userIds)
            ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $shiftStart)
            ->where('status', '!=', 'Failed')
            ->sum('amount');
    }

    /**
     * Menghitung total transaksi penarikan tunai sejak shift dibuka.
     * Jika $specificUserId diisi, hanya menghitung kontribusi teller tersebut.
     */
    public function calculateTotalWithdrawals(?int $specificUserId = null): float
    {
        $userIds = $specificUserId ? [$specificUserId] : $this->getParticipatingUserIds();
        $shiftStart = $this->opened_at ?? $this->created_at;

        return (float) Transaction::whereIn('user_id', $userIds)
            ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $shiftStart)
            ->where('status', '!=', 'Failed')
            ->sum('amount');
    }

    /**
     * [UJIAN / PENGUJI - MODEL 3]: Perhitungan Kas Fisik Real-time di Laci Bersama (calculateCurrentDrawerCash)
     * Rumus Akurat: Modal Kas Awal + Total Setoran Seluruh Teller - Total Penarikan Seluruh Teller
     */
    public function calculateCurrentDrawerCash(): float
    {
        $saldoAwal = (float) ($this->opening_balance ?? 0);
        $totalSetoran = $this->calculateTotalDeposits();
        $totalPenarikan = $this->calculateTotalWithdrawals();

        return max(0, $saldoAwal + $totalSetoran - $totalPenarikan);
    }

    /**
     * Menghitung saldo kas sistem akhir (bisa negatif jika terjadi anomali, untuk rekonsiliasi selisih).
     */
    public function calculateSystemBalance(): float
    {
        $saldoAwal = (float) ($this->opening_balance ?? 0);
        return $saldoAwal + $this->calculateTotalDeposits() - $this->calculateTotalWithdrawals();
    }

    /**
     * [UJIAN / PENGUJI - MODEL 4]: Dual Breakdown Rekonsiliasi Transaksi Per Teller (getBreakdownPerTeller)
     * Menghasilkan statistik transparan perorangan (berapa rupiah disetor/ditarik Teller 1 vs Teller 2)
     */
    public function getBreakdownPerTeller(): array
    {
        $tellers = $this->getParticipatingTellers();
        $shiftStart = $this->opened_at ?? $this->created_at;
        $breakdown = [];

        foreach ($tellers as $teller) {
            $depositQuery = Transaction::where('user_id', $teller->id)
                ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
                ->where('created_at', '>=', $shiftStart)
                ->where('status', '!=', 'Failed');

            $withdrawQuery = Transaction::where('user_id', $teller->id)
                ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
                ->where('created_at', '>=', $shiftStart)
                ->where('status', '!=', 'Failed');

            $breakdown[] = [
                'user_id'          => $teller->id,
                'name'             => $teller->officer_name ?: $teller->username,
                'is_opener'        => ($teller->id === $this->user_id),
                'deposit_count'    => (int) $depositQuery->count(),
                'deposit_total'    => (float) $depositQuery->sum('amount'),
                'withdrawal_count' => (int) $withdrawQuery->count(),
                'withdrawal_total' => (float) $withdrawQuery->sum('amount'),
                'net_cash_flow'    => (float) $depositQuery->sum('amount') - (float) $withdrawQuery->sum('amount'),
            ];
        }

        return $breakdown;
    }
}
