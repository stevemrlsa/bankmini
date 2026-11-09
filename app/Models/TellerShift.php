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
     * Mengambil shift yang saat ini sedang aktif (status 'Open').
     * Jika ada shift aktif, shift tersebut menjadi Shift Bersama bagi seluruh loket.
     */
    public static function getActiveOpenShift(): ?self
    {
        return self::where('status', 'Open')->latest()->first();
    }

    /**
     * Menambahkan teller kedua/tambahan ke dalam shift bersama jika belum terdaftar.
     */
    public function attachTeller(int $userId): void
    {
        if ($this->user_id === $userId) {
            return;
        }

        $shared = is_array($this->shared_user_ids) ? $this->shared_user_ids : [];
        if (!in_array($userId, $shared)) {
            $shared[] = $userId;
            $this->shared_user_ids = array_values(array_unique($shared));
            
            // Simpan secara aman jika kolom shared_user_ids sudah ada di database
            try {
                $this->save();
            } catch (\Throwable $e) {
                // Fallback jika migrasi belum dijalankan
            }
        }
    }

    /**
     * Mengambil daftar ID seluruh petugas teller yang bertugas dalam shift bersama ini.
     */
    public function getParticipatingUserIds(): array
    {
        $ids = [$this->user_id];
        if (!empty($this->shared_user_ids)) {
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
     * Menghitung sisa uang kas fisik nyata yang harus ada di laci bersama saat ini.
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
     * Mendapatkan rincian statistik kontribusi masing-masing teller dalam shift bersama.
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
