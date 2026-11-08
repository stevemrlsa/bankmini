<?php

namespace App\Services;

use App\Models\DailyReport;
use App\Models\TellerShift;
use App\Models\Transaction;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DailyReportService
{
    /**
     * Otomatisasi pengiriman laporan harian teller pada pukul 16:30
     * Jika teller belum mengirim laporan harian secara manual:
     * - Kas fisik laci dikosongkan (null) sebagai penanda input otomatis
     * - Status shift diubah menjadi 'Closed'
     * - Laporan disinkronisasi ke tabel daily_reports dengan status 'pending'
     * - Dicatat ke AuditLog sistem
     */
    public static function autoClosePendingShifts(): int
    {
        $now = Carbon::now('Asia/Jakarta');
        $currentTime = $now->format('H:i');
        $todayDate = $now->toDateString();

        // Cari shift kasir yang masih 'Open'
        // Kriteria:
        // 1. Shift yang dibuka di hari sebelumnya dan belum ditutup
        // 2. ATAU Shift hari ini jika waktu sudah mencapai / melewati pukul 16:30
        $openShifts = TellerShift::where('status', 'Open')
            ->where(function ($q) use ($todayDate, $currentTime) {
                $q->whereDate('opened_at', '<', $todayDate);
                if ($currentTime >= '16:30') {
                    $q->orWhereDate('opened_at', $todayDate);
                }
            })
            ->get();

        if ($openShifts->isEmpty()) {
            return 0;
        }

        $closedCount = 0;

        foreach ($openShifts as $shift) {
            try {
                $teller = $shift->user;
                $tellerName = $teller ? ($teller->officer_name ?: $teller->username) : ('Teller #' . $shift->user_id);

                $saldoAwal = (float) $shift->opening_balance;

                $totalSetoran = (float) Transaction::where('user_id', $shift->user_id)
                    ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
                    ->where('created_at', '>=', $shift->opened_at)
                    ->where('status', '!=', 'Failed')
                    ->sum('amount');

                $totalPenarikan = (float) Transaction::where('user_id', $shift->user_id)
                    ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
                    ->where('created_at', '>=', $shift->opened_at)
                    ->where('status', '!=', 'Failed')
                    ->sum('amount');

                $targetSaldo = $saldoAwal + $totalSetoran - $totalPenarikan;

                $autoNote = "[Sistem Otomatis 16:30] Laporan dikirim otomatis oleh sistem karena teller belum mengirim laporan harian hingga pukul 16:30. Kas fisik laci dikosongkan untuk verifikasi fisik manual.";
                if (!empty($shift->notes)) {
                    $autoNote = $shift->notes . ' | ' . $autoNote;
                }

                // 1. Update TellerShift: kas fisik laci (closing_balance) dikosongkan (null)
                $shift->update([
                    'closing_balance' => null,
                    'system_balance'  => $targetSaldo,
                    'difference'      => null,
                    'notes'           => $autoNote,
                    'status'          => 'Closed',
                    'closed_at'       => $now,
                ]);

                // 2. Buat atau perbarui DailyReport: closing_balance_physical dikosongkan (null)
                DailyReport::create([
                    'teller_id'                => $shift->user_id,
                    'report_date'              => $shift->opened_at ? Carbon::parse($shift->opened_at)->toDateString() : $todayDate,
                    'opening_balance'          => $saldoAwal,
                    'total_deposits'           => $totalSetoran,
                    'total_withdrawals'        => $totalPenarikan,
                    'closing_balance_system'   => $targetSaldo,
                    'closing_balance_physical' => null, // Wajib dikosongkan sesuai permintaan user
                    'notes'                    => $autoNote,
                    'status'                   => 'pending',
                ]);

                // 3. Catat di Audit Log Sistem
                AuditLog::record(
                    'TUTUP_SHIFT_OTOMATIS',
                    'Rekonsiliasi Kas',
                    "Sistem otomatis menutup shift dan mengirimkan laporan harian {$tellerName} (Saldo Sistem: Rp " . number_format($targetSaldo, 0, ',', '.') . ", Kas Fisik Laci: Dikosongkan).",
                    $shift->user_id,
                    'Sistem Otomatis (16:30)'
                );

                $closedCount++;
            } catch (\Throwable $e) {
                Log::error("Gagal auto-close shift ID {$shift->id}: " . $e->getMessage());
            }
        }

        return $closedCount;
    }
}
