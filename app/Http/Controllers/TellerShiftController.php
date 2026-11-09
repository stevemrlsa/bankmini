<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TellerShift;
use App\Models\Transaction;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class TellerShiftController extends Controller
{
    /**
     * Tampilkan form Buka Laci Kas / Modal Awal Shift.
     * Jika sudah ada shift aktif yang dibuka oleh rekan teller (Shift Bersama),
     * teller langsung dihubungkan ke shift aktif tersebut.
     */
    public function create()
    {
        $activeShift = TellerShift::getActiveOpenShift();

        if ($activeShift) {
            $activeShift->attachTeller(auth()->id());
            $opener = $activeShift->user;
            $openerName = $opener ? ($opener->officer_name ?: $opener->username) : 'Teller';

            return redirect()->route('teller.dashboard')
                ->with('info', "Laci kas hari ini sudah dibuka oleh {$openerName} (Modal Awal: Rp " . number_format($activeShift->opening_balance, 0, ',', '.') . "). Anda otomatis terhubung ke Shift Bersama!");
        }

        return view('teller.shift.create');
    }

    /**
     * Simpan data modal awal dan mulai shift baru.
     */
    public function store(Request $request)
    {
        // Bersihkan tanda titik pemisah ribuan rupiah jika diinput berformat rupiah
        $rawOpening = str_replace('.', '', (string) $request->input('opening_balance', ''));
        $request->merge(['opening_balance' => $rawOpening]);

        $request->validate([
            'opening_balance' => 'required|numeric|min:1000',
        ], [
            'opening_balance.required' => 'Nominal kas fisik awal laci wajib diisi dan tidak boleh kosong.',
            'opening_balance.numeric'  => 'Nominal kas fisik awal harus berupa angka.',
            'opening_balance.min'      => 'Kas awal laci fisik wajib diisi minimal Rp 1.000 dan tidak boleh Rp 0. Masukkan uang fisik riil yang ada di laci Anda saat memulai shift.',
        ]);

        $activeShift = TellerShift::getActiveOpenShift();

        if ($activeShift) {
            $activeShift->attachTeller(auth()->id());
            return redirect()->route('teller.dashboard')
                ->with('info', 'Shift kasir sudah dalam status terbuka oleh rekan teller. Anda terhubung ke Shift Bersama.');
        }

        $shiftData = [
            'user_id'         => auth()->id(),
            'opening_balance' => (float) $rawOpening,
            'status'          => 'Open',
            'opened_at'       => now(),
        ];

        if (Schema::hasColumn('teller_shifts', 'shared_user_ids')) {
            $shiftData['shared_user_ids'] = [auth()->id()];
        }

        TellerShift::create($shiftData);

        \App\Models\AuditLog::record(
            'BUKA_LACI_SHIFT',
            'Shift Kasir',
            'Membuka laci kas dengan modal awal Rp ' . number_format((float) $rawOpening, 0, ',', '.'),
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
        );

        return redirect()->route('teller.dashboard')
            ->with('success', 'Laci kas berhasil dibuka dengan modal awal Rp ' . number_format((float) $rawOpening, 0, ',', '.') . '. Selamat bertugas!');
    }

    /**
     * Tambah uang kas ke laci saat shift sedang aktif (ketika uang kas laci menipis / habis).
     */
    public function topUp(Request $request)
    {
        $rawAmount = str_replace('.', '', (string) $request->input('amount', ''));
        $request->merge(['amount' => $rawAmount]);

        $request->validate([
            'amount' => 'required|numeric|min:1000',
            'notes'  => 'nullable|string|max:255',
        ], [
            'amount.required' => 'Nominal penambahan kas laci wajib diisi.',
            'amount.numeric'  => 'Nominal penambahan kas harus berupa angka.',
            'amount.min'      => 'Nominal penambahan kas minimal Rp 1.000.',
        ]);

        $activeShift = TellerShift::getActiveOpenShift();

        if (!$activeShift) {
            return redirect()->route('teller.shift.create')
                ->with('error', 'Tidak ada shift kasir yang sedang aktif. Silakan buka laci terlebih dahulu.');
        }

        $activeShift->attachTeller(auth()->id());

        $addedAmount = (float) $request->amount;
        $activeShift->opening_balance = (float) $activeShift->opening_balance + $addedAmount;

        $tellerName = auth()->user()->officer_name ?? auth()->user()->username ?? 'Teller';
        $noteAppend = 'Top-Up Kas Laci oleh ' . $tellerName . ': +Rp ' . number_format($addedAmount, 0, ',', '.') . ' (' . now()->format('H:i') . ')';
        if ($request->filled('notes')) {
            $noteAppend .= ' - ' . $request->notes;
        }
        $activeShift->notes = $activeShift->notes
            ? ($activeShift->notes . ' | ' . $noteAppend)
            : $noteAppend;

        $activeShift->save();

        \App\Models\AuditLog::record(
            'TAMBAH_KAS_LACI',
            'Shift Kasir',
            'Menambahkan uang kas laci bersama sebesar Rp ' . number_format($addedAmount, 0, ',', '.') . ($request->filled('notes') ? (' (' . $request->notes . ')') : ''),
            auth()->id(),
            $tellerName
        );

        return redirect()->back()
            ->with('success', 'Berhasil menambahkan uang kas laci sebesar Rp ' . number_format($addedAmount, 0, ',', '.') . '! Total modal kas laci bersama telah diperbarui.');
    }

    /**
     * Tampilkan halaman konfirmasi dan input kas fisik penutupan shift (Close Drawer).
     */
    public function close()
    {
        $activeShift = TellerShift::getActiveOpenShift();

        if (!$activeShift) {
            return redirect()->back(fallback: route('teller.dashboard'))
                ->with('error', 'Tidak ada laci kasir yang sedang aktif.');
        }

        $activeShift->attachTeller(auth()->id());

        $saldoAwal = (float) ($activeShift->opening_balance ?? 0);
        $totalSetoran = $activeShift->calculateTotalDeposits();
        $totalPenarikan = $activeShift->calculateTotalWithdrawals();
        $targetSaldo = $activeShift->calculateSystemBalance();
        $breakdown = $activeShift->getBreakdownPerTeller();
        $participatingTellers = $activeShift->getParticipatingTellers();

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
            'estimatedSystemBalance',
            'breakdown',
            'participatingTellers'
        ));
    }

    /**
     * Proses penutupan shift kasir bersama, hitung saldo sistem, dan kunci shift.
     */
    public function endShift(Request $request)
    {
        // Support both physical_cash and closing_balance
        if (!$request->has('physical_cash') && $request->has('closing_balance')) {
            $request->merge(['physical_cash' => $request->closing_balance]);
        }

        if ($request->has('physical_cash')) {
            $rawPhysical = str_replace('.', '', (string) $request->input('physical_cash'));
            $request->merge(['physical_cash' => $rawPhysical]);
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

        $activeShift->attachTeller(auth()->id());

        // 3. SECURITY CRITICAL: Strictly calculate using all participating tellers in the shared shift
        $saldoAwal = (float) ($activeShift->opening_balance ?? 0);
        $totalSetoran = $activeShift->calculateTotalDeposits();
        $totalPenarikan = $activeShift->calculateTotalWithdrawals();
        $targetSaldo = $activeShift->calculateSystemBalance();

        // 4. Calculate difference
        $selisih = (float)$request->physical_cash - $targetSaldo;

        // 5. Validate notes: If $selisih != 0, enforce notes
        if ($selisih != 0 && empty($request->notes)) {
            return back()
                ->withErrors(['notes' => 'Catatan wajib diisi karena terdapat selisih kas.'])
                ->with('error', 'Catatan wajib diisi karena terdapat selisih kas.')
                ->withInput();
        }

        // 6. Buat ringkasan kontribusi teller
        $breakdown = $activeShift->getBreakdownPerTeller();
        $tellerNames = collect($breakdown)->pluck('name')->implode(', ');
        $breakdownSummary = collect($breakdown)->map(function($b) {
            return $b['name'] . ': Setor Rp ' . number_format($b['deposit_total'], 0, ',', '.') . ' (' . $b['deposit_count'] . ' trx), Tarik Rp ' . number_format($b['withdrawal_total'], 0, ',', '.') . ' (' . $b['withdrawal_count'] . ' trx)';
        })->implode(' | ');

        $finalNotes = $request->notes;
        $sharedNoteAppend = "Petugas Shift Bersama: [{$tellerNames}]. Ditutup oleh: " . (auth()->user()->officer_name ?: auth()->user()->username) . ". Rincian: " . $breakdownSummary;
        $finalNotes = $finalNotes ? ($finalNotes . " | " . $sharedNoteAppend) : $sharedNoteAppend;

        // 7. Update TellerShift record
        $updateData = [
            'closing_balance' => $request->physical_cash,
            'system_balance'  => $targetSaldo,
            'difference'      => $selisih,
            'notes'           => $finalNotes,
            'status'          => 'Closed',
            'closed_at'       => now(),
        ];

        if (Schema::hasColumn('teller_shifts', 'closed_by')) {
            $updateData['closed_by'] = auth()->id();
        }

        $activeShift->update($updateData);

        // Synchronize with DailyReport table if available
        DailyReport::create([
            'teller_id'                => $activeShift->user_id, // Opener sebagai anchor
            'report_date'              => now()->toDateString(),
            'opening_balance'          => $saldoAwal,
            'total_deposits'           => $totalSetoran,
            'total_withdrawals'        => $totalPenarikan,
            'closing_balance_system'   => $targetSaldo,
            'closing_balance_physical' => $request->physical_cash,
            'notes'                    => $finalNotes,
            'status'                   => 'pending',
        ]);

        \App\Models\AuditLog::record(
            'TUTUP_SHIFT_KAS',
            'Rekonsiliasi Kas',
            'Mengirim laporan penutupan kas laci bersama (' . $tellerNames . ') dengan uang fisik Rp ' . number_format($request->physical_cash, 0, ',', '.') . ' (Sistem: Rp ' . number_format($targetSaldo, 0, ',', '.') . ', Selisih: Rp ' . number_format($selisih, 0, ',', '.') . ')',
            auth()->id(),
            auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Teller'
        );

        return redirect()->route('teller.dashboard')
            ->with('success', 'Laporan penutupan kas laci bersama berhasil dikirim ke Administrator.');
    }

    /**
     * Endpoint API Live Status untuk sinkronisasi kas laci real-time antar teller se-shift.
     */
    public function liveStatus()
    {
        $activeShift = TellerShift::getActiveOpenShift();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'status'  => 'Closed',
                'message' => 'Tidak ada shift yang aktif.',
            ]);
        }

        if (auth()->check() && auth()->user()->role === 'Teller') {
            $activeShift->attachTeller(auth()->id());
        }

        $drawerCash = $activeShift->calculateCurrentDrawerCash();
        $totalDeposits = $activeShift->calculateTotalDeposits();
        $totalWithdrawals = $activeShift->calculateTotalWithdrawals();
        $tellers = $activeShift->getParticipatingTellers()->map(function($u) {
            return [
                'id'   => $u->id,
                'name' => $u->officer_name ?: $u->username,
            ];
        });

        // Ambil transaksi terakhir pada shift ini
        $userIds = $activeShift->getParticipatingUserIds();
        $latestTx = Transaction::with('user')
            ->whereIn('user_id', $userIds)
            ->where('created_at', '>=', $activeShift->opened_at ?? $activeShift->created_at)
            ->where('status', '!=', 'Failed')
            ->latest()
            ->first();

        $latestTxData = null;
        if ($latestTx) {
            $txTellerName = $latestTx->user ? ($latestTx->user->officer_name ?: $latestTx->user->username) : 'Teller';
            $latestTxData = [
                'id'           => $latestTx->id,
                'type'         => $latestTx->transaction_type,
                'amount'       => (float) $latestTx->amount,
                'formatted'    => 'Rp ' . number_format($latestTx->amount, 0, ',', '.'),
                'teller_id'    => $latestTx->user_id,
                'teller_name'  => $txTellerName,
                'time'         => $latestTx->created_at->timezone('Asia/Jakarta')->format('H:i:s'),
                'is_my_tx'     => ($latestTx->user_id === auth()->id()),
            ];
        }

        return response()->json([
            'success'               => true,
            'status'                => 'Open',
            'shift_id'              => $activeShift->id,
            'opening_balance'       => (float) $activeShift->opening_balance,
            'current_drawer_cash'   => $drawerCash,
            'formatted_drawer_cash' => 'Rp ' . number_format($drawerCash, 0, ',', '.'),
            'total_deposits'        => $totalDeposits,
            'total_withdrawals'     => $totalWithdrawals,
            'tellers'               => $tellers,
            'tellers_count'         => $tellers->count(),
            'is_shared'             => ($tellers->count() > 1),
            'latest_tx'             => $latestTxData,
            'timestamp'             => now()->timestamp,
        ]);
    }
}
