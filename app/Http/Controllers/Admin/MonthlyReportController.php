<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class MonthlyReportController extends Controller
{
    /**
     * Menampilkan laporan transaksi bulanan dalam format tabel
     */
    public function index(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $query = Transaction::with(['saver', 'user'])
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->orderBy('transaction_date', 'desc');

        $transactions = $query->get();

        $totalDeposits = $transactions->where('transaction_type', 'Deposit')->sum('amount');
        $totalWithdrawals = $transactions->where('transaction_type', 'Withdrawal')->sum('amount');
        $totalVault = $transactions->where('transaction_type', 'Vault_Transfer')->sum('amount');
        $netSavings = $totalDeposits - $totalWithdrawals;

        return view('admin.reports.monthly', compact(
            'transactions',
            'month',
            'year',
            'totalDeposits',
            'totalWithdrawals',
            'totalVault',
            'netSavings'
        ));
    }

    /**
     * Export laporan bulanan ke format file PDF
     */
    public function exportPdf(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);

        $transactions = Transaction::with(['saver', 'user'])
            ->whereMonth('transaction_date', $month)
            ->whereYear('transaction_date', $year)
            ->orderBy('transaction_date', 'asc')
            ->get();

        $totalDeposits = $transactions->where('transaction_type', 'Deposit')->sum('amount');
        $totalWithdrawals = $transactions->where('transaction_type', 'Withdrawal')->sum('amount');
        $totalVault = $transactions->where('transaction_type', 'Vault_Transfer')->sum('amount');
        $netSavings = $totalDeposits - $totalWithdrawals;

        $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');

        $pdf = Pdf::loadView('admin.reports.monthly_pdf', compact(
            'transactions',
            'month',
            'year',
            'monthName',
            'totalDeposits',
            'totalWithdrawals',
            'totalVault',
            'netSavings'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("Laporan_Bulanan_BankMini_{$year}_{$month}.pdf");
    }
}
