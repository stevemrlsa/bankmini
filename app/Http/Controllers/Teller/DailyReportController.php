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
        // 1. Find the active shift for the authenticated teller (supports shared shift)
        $activeShift = TellerShift::getActiveOpenShift()
            ?? TellerShift::where('user_id', Auth::id())->where('status', 'Open')->first();

        if (!$activeShift) {
            return redirect()->back(fallback: route('teller.dashboard'))
                ->with('error', 'Tidak ada laci kasir yang sedang aktif.');
        }

        $activeShift->attachTeller(Auth::id());

        // 2. Calculate shift balances consistently with shared shift logic
        $saldoAwal = (float) ($activeShift->opening_balance ?? 0);
        $totalSetoran = $activeShift->calculateTotalDeposits();
        $totalPenarikan = $activeShift->calculateTotalWithdrawals();
        $targetSaldo = $activeShift->calculateSystemBalance();

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
        // 0. Hitung otomatis rincian pecahan uang fisik jika diinput
        $denomData = DailyReport::normalizeDenominations($request->input('denominations'));

        // Support both physical_cash and closing_balance_physical input names
        if (!$request->has('physical_cash') && $request->has('closing_balance_physical')) {
            $request->merge(['physical_cash' => $request->closing_balance_physical]);
        }

        if ($request->has('physical_cash')) {
            $rawPhysical = str_replace('.', '', (string) $request->input('physical_cash'));
            $request->merge(['physical_cash' => $rawPhysical]);
        }

        if ($denomData['has_any'] && ($denomData['total'] > 0 || !$request->filled('physical_cash'))) {
            $request->merge(['physical_cash' => $denomData['total']]);
        }

        // 1. Validate request
        $request->validate([
            'physical_cash' => 'required|numeric',
            'notes'         => 'nullable|string',
        ], [
            'physical_cash.required' => 'Nominal uang fisik di laci kasir wajib diisi.',
            'physical_cash.numeric'  => 'Nominal uang fisik harus berupa angka valid.',
        ]);

        // 2. Get active shared shift
        $activeShift = TellerShift::getActiveOpenShift();

        if (!$activeShift) {
            return redirect()->back(fallback: route('teller.dashboard'))
                ->with('error', 'Tidak ada laci kasir yang sedang aktif.');
        }

        $activeShift->attachTeller(Auth::id());

        // 3. SECURITY CRITICAL: Strictly calculate using all participating tellers in the shared shift
        $saldoAwal = (float) ($activeShift->opening_balance ?? 0);
        $totalSetoran = $activeShift->calculateTotalDeposits();
        $totalPenarikan = $activeShift->calculateTotalWithdrawals();
        $targetSaldo = $activeShift->calculateSystemBalance();

        // 4. Calculate difference
        $selisih = (float)$request->physical_cash - $targetSaldo;

        // 5. Validate notes: If $selisih != 0, enforce that $request->notes is required
        if ($selisih != 0 && empty($request->notes)) {
            return back()
                ->withErrors(['notes' => 'Catatan wajib diisi karena terdapat selisih kas.'])
                ->with('error', 'Catatan wajib diisi karena terdapat selisih kas.')
                ->withInput();
        }

        // 6. Ringkasan petugas shift bersama
        $breakdown = $activeShift->getBreakdownPerTeller();
        $tellerNames = collect($breakdown)->pluck('name')->implode(', ');
        $breakdownSummary = collect($breakdown)->map(function($b) {
            return $b['name'] . ': Setor Rp ' . number_format($b['deposit_total'], 0, ',', '.') . ' (' . $b['deposit_count'] . ' trx), Tarik Rp ' . number_format($b['withdrawal_total'], 0, ',', '.') . ' (' . $b['withdrawal_count'] . ' trx)';
        })->implode(' | ');

        $finalNotes = $request->notes;
        $sharedNoteAppend = "Petugas Shift Bersama: [{$tellerNames}]. Ditutup oleh: " . (Auth::user()->officer_name ?: Auth::user()->username) . ". Rincian: " . $breakdownSummary;
        if (!empty($denomData['summary_text'])) {
            $sharedNoteAppend .= " | " . $denomData['summary_text'];
        }
        $finalNotes = $finalNotes ? ($finalNotes . " | " . $sharedNoteAppend) : $sharedNoteAppend;

        // 7. Update the TellerShift record
        $updateData = [
            'closing_balance' => $request->physical_cash,
            'system_balance'  => $targetSaldo,
            'difference'      => $selisih,
            'notes'           => $finalNotes,
            'status'          => 'Closed',
            'closed_at'       => now(),
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('teller_shifts', 'closed_by')) {
            $updateData['closed_by'] = Auth::id();
        }
        if ($denomData['has_any'] && \Illuminate\Support\Facades\Schema::hasColumn('teller_shifts', 'cash_denominations')) {
            $updateData['cash_denominations'] = $denomData['counts'];
        }
        $activeShift->update($updateData);

        // Synchronize with DailyReport table for administrative approval workflow
        $reportData = [
            'teller_id'                => $activeShift->user_id,
            'report_date'              => now()->toDateString(),
            'opening_balance'          => $saldoAwal,
            'total_deposits'           => $totalSetoran,
            'total_withdrawals'        => $totalPenarikan,
            'closing_balance_system'   => $targetSaldo,
            'closing_balance_physical' => $request->physical_cash,
            'notes'                    => $finalNotes,
            'status'                   => 'pending',
        ];
        if ($denomData['has_any'] && \Illuminate\Support\Facades\Schema::hasColumn('daily_reports', 'cash_denominations')) {
            $reportData['cash_denominations'] = $denomData['counts'];
        }
        DailyReport::create($reportData);

        \App\Models\AuditLog::record(
            'TUTUP_SHIFT_KAS',
            'Rekonsiliasi Kas',
            'Mengirim laporan penutupan kas laci dengan uang fisik Rp ' . number_format($request->physical_cash, 0, ',', '.') . ' (Sistem: Rp ' . number_format($targetSaldo, 0, ',', '.') . ', Selisih: Rp ' . number_format($selisih, 0, ',', '.') . ')',
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
        );

        return redirect()->route('teller.dashboard')
            ->with('success', 'Laporan penutupan kas berhasil dikirim ke Administrator.');
    }
}
