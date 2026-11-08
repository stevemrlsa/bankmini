<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\TellerShift;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Services\DailyReportService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ApprovalController extends Controller
{
    /**
     * Menampilkan daftar laporan penutupan kas & shift laci kasir teller yang menunggu approval
     */
    public function indexApproval()
    {
        // Jalankan auto-close shift otomatis pada pukul 16:30 atau shift tertinggal
        DailyReportService::autoClosePendingShifts();

        // Sinkronisasi otomatis jika ada data TellerShift yang belum tercatat di DailyReport
        try {
            $unSyncedShifts = TellerShift::whereIn('status', ['Closed', 'Approved'])->get();
            foreach ($unSyncedShifts as $shift) {
                $shiftDate = \Carbon\Carbon::parse($shift->closed_at ?? $shift->updated_at)->toDateString();
                $exists = DailyReport::where('teller_id', $shift->user_id)
                    ->where('report_date', $shiftDate)
                    ->exists();

                if (!$exists) {
                    DailyReport::create([
                        'teller_id'                => $shift->user_id,
                        'report_date'              => $shiftDate,
                        'opening_balance'          => $shift->opening_balance ?? 0,
                        'total_deposits'           => 0,
                        'total_withdrawals'        => 0,
                        'closing_balance_system'   => $shift->system_balance ?? ($shift->closing_balance ?? 0),
                        'closing_balance_physical' => $shift->closing_balance ?? 0,
                        'notes'                    => $shift->notes,
                        'status'                   => $shift->status === 'Approved' ? 'approved' : 'pending',
                        'admin_id'                 => $shift->approved_by,
                        'approval_at'              => $shift->approved_at,
                        'created_at'               => $shift->closed_at ?? $shift->created_at,
                        'updated_at'               => $shift->updated_at,
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // Silently continue if sync table not ready
        }

        $reports = DailyReport::with(['teller', 'admin'])->orderBy('created_at', 'desc')->get();
        return view('admin.approval.index', compact('reports'));
    }

    /**
     * Menyetujui penutupan kas dan memindahkan uang fisik ke brankas utama (Vault Transfer)
     */
    public function approveKas(Request $request, string $id)
    {
        try {
            DB::transaction(function() use ($id) {
                $report = DailyReport::findOrFail($id);

                // 1. Update Status DailyReport
                $report->update([
                    'status'      => 'approved',
                    'admin_id'    => auth()->id(), // Disetujui oleh Administrator
                    'approval_at' => now(),
                ]);

                // 2. Sinkronkan status TellerShift terkait menjadi Approved
                TellerShift::where('user_id', $report->teller_id)
                    ->where('status', 'Closed')
                    ->latest()
                    ->first()
                    ?->update([
                        'status'      => 'Approved',
                        'approved_by' => auth()->id(),
                        'approved_at' => now(),
                    ]);

                $amountTransfer = $report->closing_balance_physical;

                // 3. Buat Transaksi Internal Brankas (Vault Transfer) HANYA jika amount > 0
                // Mencegah error SQL 3819 check constraint jika saldo fisik negatif atau nol
                if ($amountTransfer > 0) {
                    $transaction = Transaction::create([
                        'saver_id'         => null, 
                        'user_id'          => auth()->id(),
                        'transaction_date' => now(),
                        'transaction_type' => 'Vault_Transfer', 
                        'amount'           => $amountTransfer,
                        'status'           => 'Approved',
                    ]);

                    // 4. Jurnal Double-Entry Pemindahan ke Brankas Utama:
                    // Akun 102 (Brankas Utama) bertambah [Debit]
                    // Akun 101 (Kas Loket Teller) berkurang [Kredit]
                    if (Schema::hasTable('accounting_journals')) {
                        DB::table('accounting_journals')->insert([
                            [
                                'transaction_id' => $transaction->id,
                                'account_code'   => '102', // Brankas Utama (Debit)
                                'position'       => 'Debit',
                                'amount'         => $amountTransfer,
                                'created_at'     => now(),
                                'updated_at'     => now(),
                            ],
                            [
                                'transaction_id' => $transaction->id,
                                'account_code'   => '101', // Kas Loket Teller (Kredit)
                                'position'       => 'Credit',
                                'amount'         => $amountTransfer,
                                'created_at'     => now(),
                                'updated_at'     => now(),
                            ]
                        ]);
                    }
                }

                // Catat ke Audit Log Sistem
                AuditLog::record(
                    'APPROVE_KAS',
                    'Approval Kas Teller',
                    'Menyetujui laporan harian kasir ' . ($report->teller?->officer_name ?: $report->teller?->username) . ' periode ' . $report->report_date . ($amountTransfer > 0 ? ' dan memindahkan Rp ' . number_format($amountTransfer, 0, ',', '.') . ' ke Brankas Utama' : '') . '.'
                );
            });

            return back()->with('success', 'Laporan kas teller disetujui! ' . (DailyReport::find($id)?->closing_balance_physical > 0 ? 'Dana resmi dipindahkan ke Brankas Utama dan tercatat dalam buku jurnal.' : 'Status laporan telah diperbarui menjadi Disetujui.'));
            
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat memproses approval: ' . $e->getMessage());
        }
    }

    /**
     * Menolak laporan penutupan kas jika ada sengketa / ketidakcocokan
     */
    public function rejectKas(Request $request, string $id)
    {
        $report = DailyReport::findOrFail($id);
        
        $rejectReason = $request->notes ?? 'Laporan ditolak oleh Administrator.';
        $report->update([
            'status'      => 'rejected',
            'admin_id'    => auth()->id(), // Ditolak oleh Administrator
            'notes'       => $rejectReason,
            'approval_at' => now(),
        ]);

        // Kembalikan shift kasir ke Open agar teller dapat memeriksa ulang kas laci
        TellerShift::where('user_id', $report->teller_id)
            ->where('status', 'Closed')
            ->latest()
            ->first()
            ?->update([
                'status' => 'Open',
                'notes'  => $rejectReason,
            ]);

        // Catat ke Audit Log Sistem
        AuditLog::record(
            'REJECT_KAS',
            'Approval Kas Teller',
            'Menolak laporan kas harian ' . ($report->teller?->officer_name ?: $report->teller?->username) . ' periode ' . $report->report_date . ' dengan catatan: ' . $rejectReason
        );

        return back()->with('success', 'Laporan penutupan kas telah ditolak untuk diselesaikan oleh Teller.');
    }

    /**
     * Mengunduh Berita Acara Rekonsiliasi & Penutupan Kas Teller dalam format PDF resmi
     */
    public function downloadPdf(string $id)
    {
        $report = DailyReport::with(['teller', 'admin'])->findOrFail($id);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.approval.pdf', compact('report'))
            ->setPaper('a4', 'portrait');

        $tellerName = preg_replace('/[^A-Za-z0-9_-]/', '_', $report->teller->officer_name ?? $report->teller->username ?? 'Teller');
        $dateStr = \Carbon\Carbon::parse($report->report_date)->format('Y-m-d');
        $filename = "Berita_Acara_Kas_{$tellerName}_{$dateStr}_#{$report->id}.pdf";

        return $pdf->download($filename);
    }
}
