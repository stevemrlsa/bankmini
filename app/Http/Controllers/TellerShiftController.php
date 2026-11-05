<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TellerShift;
use App\Models\Transaction;
use App\Models\DailyReport;

class TellerShiftController extends Controller
{
    /**
     * Tampilkan form Buka Laci Kas / Modal Awal Shift.
     */
    public function create()
    {
        $activeShift = TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->first();

        if ($activeShift) {
            return redirect()->route('teller.dashboard')
                ->with('info', 'Shift Anda sudah aktif dan laci kas telah terbuka.');
        }

        return view('teller.shift.create');
    }

    /**
     * Simpan data modal awal dan mulai shift baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'opening_balance' => 'required|numeric|min:0',
        ], [
            'opening_balance.required' => 'Nominal modal awal (buka laci) wajib diisi.',
            'opening_balance.numeric'  => 'Nominal modal awal harus berupa angka.',
            'opening_balance.min'      => 'Nominal modal awal tidak boleh bernilai negatif.',
        ]);

        $activeShift = TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->first();

        if ($activeShift) {
            return redirect()->route('teller.dashboard')
                ->with('warning', 'Shift kasir Anda sudah dalam status terbuka.');
        }

        TellerShift::create([
            'user_id'         => auth()->id(),
            'opening_balance' => $request->opening_balance,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        return redirect()->route('teller.dashboard')
            ->with('success', 'Laci kas berhasil dibuka. Selamat bertugas!');
    }

    /**
     * Tampilkan halaman konfirmasi dan input kas fisik penutupan shift (Close Drawer).
     */
    public function close()
    {
        // 1. Find the active shift for the authenticated teller
        $activeShift = TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->first();

        if (!$activeShift) {
            return redirect()->back(fallback: route('teller.dashboard'))
                ->with('error', 'Tidak ada laci kasir yang sedang aktif.');
        }

        // 2. Extract opening balance
        $saldoAwal = $activeShift->opening_balance;

        // 3. Calculate total successful deposits since shift opened
        $totalSetoran = Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        // 4. Calculate total successful withdrawals since shift opened
        $totalPenarikan = Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        // 5. Calculate target system balance
        $targetSaldo = $saldoAwal + $totalSetoran - $totalPenarikan;

        // Legacy aliases
        $totalDeposit           = $totalSetoran;
        $totalWithdrawal        = $totalPenarikan;
        $estimatedSystemBalance = $targetSaldo;

        return view('teller.shift.close', compact(
            'activeShift',
            'saldoAwal',
            'totalSetoran',
            'totalPenarikan',
            'targetSaldo',
            'totalDeposit',
            'totalWithdrawal',
            'estimatedSystemBalance'
        ));
    }

    /**
     * Proses penutupan shift kasir, hitung saldo sistem, dan kunci shift.
     */
    public function endShift(Request $request)
    {
        // Support both physical_cash and closing_balance
        if (!$request->has('physical_cash') && $request->has('closing_balance')) {
            $request->merge(['physical_cash' => $request->closing_balance]);
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
        $activeShift = TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->first();

        if (!$activeShift) {
            return redirect()->back(fallback: route('teller.dashboard'))
                ->with('error', 'Tidak ada laci kasir yang sedang aktif.');
        }

        // 3. SECURITY CRITICAL: Strictly recalculate calculations server-side
        $saldoAwal = $activeShift->opening_balance;

        $totalSetoran = Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        $totalPenarikan = Transaction::where('user_id', auth()->id())
            ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
            ->where('created_at', '>=', $activeShift->opened_at)
            ->where('status', '!=', 'Failed')
            ->sum('amount');

        $targetSaldo = $saldoAwal + $totalSetoran - $totalPenarikan;

        // 4. Calculate difference
        $selisih = $request->physical_cash - $targetSaldo;

        // 5. Validate notes: If $selisih != 0, enforce notes
        if ($selisih != 0 && empty($request->notes)) {
            return back()
                ->withErrors(['notes' => 'Catatan wajib diisi karena terdapat selisih kas.'])
                ->with('error', 'Catatan wajib diisi karena terdapat selisih kas.')
                ->withInput();
        }

        // 6. Update TellerShift record
        $activeShift->update([
            'closing_balance' => $request->physical_cash,
            'system_balance'  => $targetSaldo,
            'difference'      => $selisih,
            'notes'           => $request->notes,
            'status'          => 'Closed',
            'closed_at'       => now(),
        ]);

        // Synchronize with DailyReport table if available
        DailyReport::create([
            'teller_id'                => auth()->id(),
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
