<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DailyReport;
use App\Models\TellerShift;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DailyReportController extends Controller
{
    /**
     * Menampilkan halaman kalkulasi penutupan kas harian teller
     */
    public function index()
    {
        // 1. Find the active shift for the authenticated teller
        $activeShift = TellerShift::where('user_id', Auth::id())
            ->where('status', 'Open')
            ->first();

        if (!$activeShift) {
            return redirect()->back(fallback: route('teller.dashboard'))
                ->with('error', 'Tidak ada laci kasir yang sedang aktif.');
        }

        // 2. Extract opening balance
        $saldoAwal = $activeShift->opening_balance;

        // 3. Calculate total successful deposits since shift opened
        $totalSetoran = Transaction::where('user_id', Auth::id())
            ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        // 4. Calculate total successful withdrawals since shift opened
        $totalPenarikan = Transaction::where('user_id', Auth::id())
            ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        // 5. Calculate target system balance
        $targetSaldo = $saldoAwal + $totalSetoran - $totalPenarikan;

        // Variable aliases for template backwards compatibility
        $openingBalance       = $saldoAwal;
        $totalDeposits        = $totalSetoran;
        $totalWithdrawals     = $totalPenarikan;
        $closingBalanceSystem = $targetSaldo;

        return view('teller.reports.create', compact(
            'activeShift',
            'saldoAwal',
            'totalSetoran',
            'totalPenarikan',
            'targetSaldo',
            'openingBalance',
            'totalDeposits',
            'totalWithdrawals',
            'closingBalanceSystem'
        ));
    }

    /**
     * Menyimpan / Mengirim Draf Laporan Harian ke Administrator
     */
    public function store(Request $request)
    {
        // Support both physical_cash and closing_balance_physical input names
        if (!$request->has('physical_cash') && $request->has('closing_balance_physical')) {
            $request->merge(['physical_cash' => $request->closing_balance_physical]);
        }

        // 1. Validate request
        $request->validate([
            'physical_cash' => 'required|numeric',
            'notes'         => 'nullable|string',
        ], [
            'physical_cash.required' => 'Nominal uang fisik di laci kasir wajib diisi.',
            'physical_cash.numeric'  => 'Nominal uang fisik harus berupa angka valid.',
        ]);

        // 2. Get active shift
        $activeShift = TellerShift::where('user_id', Auth::id())
            ->where('status', 'Open')
            ->first();

        if (!$activeShift) {
            return redirect()->back(fallback: route('teller.dashboard'))
                ->with('error', 'Tidak ada laci kasir yang sedang aktif.');
        }

        // 3. SECURITY CRITICAL: Strictly recalculate calculations server-side
        $saldoAwal = $activeShift->opening_balance;

        $totalSetoran = Transaction::where('user_id', Auth::id())
            ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        $totalPenarikan = Transaction::where('user_id', Auth::id())
            ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        $targetSaldo = $saldoAwal + $totalSetoran - $totalPenarikan;

        // 4. Calculate difference
        $selisih = $request->physical_cash - $targetSaldo;

        // 5. Validate notes: If $selisih != 0, enforce that $request->notes is required
        if ($selisih != 0 && empty($request->notes)) {
            return back()
                ->withErrors(['notes' => 'Catatan wajib diisi karena terdapat selisih kas.'])
                ->with('error', 'Catatan wajib diisi karena terdapat selisih kas.')
                ->withInput();
        }

        // 6. Update the TellerShift record
        $activeShift->update([
            'closing_balance' => $request->physical_cash,
            'system_balance'  => $targetSaldo,
            'difference'      => $selisih,
            'notes'           => $request->notes,
            'status'          => 'Closed',
            'closed_at'       => now(),
        ]);

        // Synchronize with DailyReport table for administrative approval workflow
        DailyReport::create([
            'teller_id'                => Auth::id(),
            'report_date'              => now()->toDateString(),
            'opening_balance'          => $saldoAwal,
            'total_deposits'           => $totalSetoran,
            'total_withdrawals'        => $totalPenarikan,
            'closing_balance_system'   => $targetSaldo,
            'closing_balance_physical' => $request->physical_cash,
            'notes'                    => $request->notes,
            'status'                   => 'pending',
        ]);

        return redirect()->route('teller.dashboard')
            ->with('success', 'Laporan penutupan kas berhasil dikirim ke Administrator.');
    }
}
