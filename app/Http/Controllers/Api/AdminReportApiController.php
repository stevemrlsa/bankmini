<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DailyReport;
use App\Models\Transaction;

class AdminReportApiController extends Controller
{
    /**
     * Menyetujui laporan harian kas teller via API oleh Administrator
     */
    public function approveDailyReport(Request $request)
    {
        $request->validate([
            'report_id' => 'nullable|exists:daily_reports,id',
            'teller_id' => 'required_without:report_id|exists:users,id',
            'date'      => 'required_without:report_id|date',
        ]);

        try {
            DB::transaction(function () use ($request, &$report) {
                if ($request->filled('report_id')) {
                    $report = DailyReport::findOrFail($request->report_id);
                } else {
                    $report = DailyReport::where('teller_id', $request->teller_id)
                        ->whereDate('report_date', $request->date)
                        ->first();

                    if (!$report) {
                        throw new \Exception('Laporan penutupan kas tidak ditemukan.');
                    }
                }

                if ($report->status === 'approved') {
                    throw new \Exception('Laporan ini sudah disetujui sebelumnya.');
                }

                // 1. Update status DailyReport
                $report->update([
                    'status'      => 'approved',
                    'admin_id'    => $request->user()->id,
                    'approval_at' => now(),
                ]);

                $amountTransfer = $report->closing_balance_physical ?? $report->total_cash ?? 0;

                // 2. Buat transaksi Vault Transfer internal HANYA jika amount > 0
                // Mencegah error SQL check constraint jika saldo fisik <= 0
                if ($amountTransfer > 0) {
                    $transaction = Transaction::create([
                        'saver_id'         => null,
                        'user_id'          => $request->user()->id,
                        'transaction_date' => now(),
                        'transaction_type' => 'Vault_Transfer',
                        'amount'           => $amountTransfer,
                        'status'           => 'Approved',
                    ]);

                    // 3. Jurnal Double-Entry: Debit Akun 102 (Brankas Utama), Credit Akun 101 (Kas Teller)
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
                            'account_code'   => '101', // Kas Teller (Credit)
                            'position'       => 'Credit',
                            'amount'         => $amountTransfer,
                            'created_at'     => now(),
                            'updated_at'     => now(),
                        ]
                    ]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Laporan harian berhasil disetujui oleh Administrator. Kas resmi dipindahkan ke Brankas Utama dan tercatat di jurnal.',
                'data'    => $report
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan approval: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menolak laporan harian kas teller via API oleh Administrator
     */
    public function rejectDailyReport(Request $request)
    {
        $request->validate([
            'report_id' => 'required|exists:daily_reports,id',
            'notes'     => 'required|string',
        ]);

        try {
            DB::transaction(function () use ($request, &$report) {
                $report = DailyReport::findOrFail($request->report_id);

                if ($report->status === 'approved') {
                    throw new \Exception('Laporan sudah disetujui sebelumnya, tidak dapat ditolak.');
                }

                $report->update([
                    'status'      => 'rejected',
                    'admin_id'    => $request->user()->id,
                    'notes'       => $request->notes ?? 'Ditolak oleh Administrator.',
                    'approval_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Laporan penutupan kas berhasil ditolak oleh Administrator.',
                'data'    => $report
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan penolakan: ' . $e->getMessage()
            ], 500);
        }
    }
}
