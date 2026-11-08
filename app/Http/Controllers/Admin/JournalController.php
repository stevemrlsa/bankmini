<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class JournalController extends Controller
{
    /**
     * Menampilkan Jurnal Umum Akuntansi (Double-Entry Ledger) Bank Mini
     */
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->toDateString());
        $account   = $request->get('account');
        $position  = $request->get('position');

        // Query Jurnal Akuntansi dengan join transaksi
        $query = DB::table('accounting_journals')
            ->leftJoin('transactions', 'accounting_journals.transaction_id', '=', 'transactions.id')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('savers', 'transactions.saver_id', '=', 'savers.id')
            ->select(
                'accounting_journals.id',
                'accounting_journals.transaction_id',
                'accounting_journals.account_code',
                'accounting_journals.position',
                'accounting_journals.amount',
                'accounting_journals.created_at',
                'transactions.transaction_type',
                'transactions.transaction_date',
                'transactions.status as transaction_status',
                'users.officer_name as officer_name',
                'users.username as officer_username',
                'savers.student_name',
                'savers.account_number'
            );

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(accounting_journals.created_at)'), [$startDate, $endDate]);
        }

        if ($account) {
            $query->where('accounting_journals.account_code', $account);
        }

        if ($position && in_array($position, ['Debit', 'Credit'])) {
            $query->where('accounting_journals.position', $position);
        }

        $journals = $query->orderBy('accounting_journals.created_at', 'desc')->get();

        // Hitung total Debit dan Kredit dari data yang terfilter
        $totalDebit  = (float) $journals->where('position', 'Debit')->sum('amount');
        $totalCredit = (float) $journals->where('position', 'Credit')->sum('amount');
        $diff        = abs($totalDebit - $totalCredit);
        $isBalanced  = ($diff < 0.01);

        // Akun-akun resmi Bank Mini
        $accountNames = [
            '101' => 'Kas di Loket Teller (Cash in Drawer)',
            '102' => 'Kas di Brankas Utama (Vault Cash)',
            '201' => 'Simpanan Tabungan Siswa (Savings Liability)',
            '401' => 'Pendapatan Operasional Kas',
            '501' => 'Beban Operasional Kas',
        ];

        return view('admin.journals.index', compact(
            'journals',
            'totalDebit',
            'totalCredit',
            'isBalanced',
            'diff',
            'startDate',
            'endDate',
            'account',
            'position',
            'accountNames'
        ));
    }

    /**
     * Cetak / Unduh Buku Jurnal Akuntansi dalam format PDF
     */
    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', now()->startOfMonth()->toDateString());
        $endDate   = $request->get('end_date', now()->toDateString());
        $account   = $request->get('account');
        $position  = $request->get('position');

        $query = DB::table('accounting_journals')
            ->leftJoin('transactions', 'accounting_journals.transaction_id', '=', 'transactions.id')
            ->leftJoin('users', 'transactions.user_id', '=', 'users.id')
            ->leftJoin('savers', 'transactions.saver_id', '=', 'savers.id')
            ->select(
                'accounting_journals.id',
                'accounting_journals.transaction_id',
                'accounting_journals.account_code',
                'accounting_journals.position',
                'accounting_journals.amount',
                'accounting_journals.created_at',
                'transactions.transaction_type',
                'users.officer_name as officer_name',
                'users.username as officer_username',
                'savers.student_name',
                'savers.account_number'
            );

        if ($startDate && $endDate) {
            $query->whereBetween(DB::raw('DATE(accounting_journals.created_at)'), [$startDate, $endDate]);
        }

        if ($account) {
            $query->where('accounting_journals.account_code', $account);
        }

        if ($position && in_array($position, ['Debit', 'Credit'])) {
            $query->where('accounting_journals.position', $position);
        }

        $journals = $query->orderBy('accounting_journals.created_at', 'asc')->get();

        $totalDebit  = (float) $journals->where('position', 'Debit')->sum('amount');
        $totalCredit = (float) $journals->where('position', 'Credit')->sum('amount');
        $isBalanced  = (abs($totalDebit - $totalCredit) < 0.01);

        $accountNames = [
            '101' => 'Kas di Loket Teller (Cash in Drawer)',
            '102' => 'Kas di Brankas Utama (Vault Cash)',
            '201' => 'Simpanan Tabungan Siswa (Savings Liability)',
            '401' => 'Pendapatan Operasional Kas',
            '501' => 'Beban Operasional Kas',
        ];

        $pdf = Pdf::loadView('admin.journals.pdf', compact(
            'journals',
            'totalDebit',
            'totalCredit',
            'isBalanced',
            'startDate',
            'endDate',
            'accountNames'
        ))->setPaper('a4', 'landscape');

        $filename = "Jurnal_Akuntansi_BankMini_{$startDate}_sampai_{$endDate}.pdf";
        return $pdf->download($filename);
    }
}

