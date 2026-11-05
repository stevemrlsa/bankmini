<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TellerShift;
use App\Models\AccountingJournal;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class AdminShiftController extends Controller
{
    /**
     * Menampilkan daftar shift teller yang berstatus Closed atau Approved.
     */
    public function index()
    {
        $shifts = TellerShift::with(['user', 'approver'])
            ->whereIn('status', ['Closed', 'Approved'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('admin.shifts.index', compact('shifts'));
    }

    /**
     * Menyetujui penutupan kas teller dan memindahkan dana fisik ke Brankas Utama (Vault Transfer).
     */
    public function approve(Request $request, $id)
    {
        $shift = TellerShift::where('status', 'Closed')->findOrFail($id);

        try {
            DB::transaction(function () use ($shift) {
                // 1. Update status shift menjadi Approved
                $shift->update([
                    'status'      => 'Approved',
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);

                $closingBalance = $shift->closing_balance ?? 0;

                // 2. Pencatatan Jurnal Double-Entry jika ada dana fisik yang dipindahkan
                if ($closingBalance > 0) {
                    // Buat rekaman transaksi internal pemindahan ke brankas
                    $transaction = Transaction::create([
                        'saver_id'         => null,
                        'user_id'          => auth()->id(),
                        'transaction_date' => now(),
                        'transaction_type' => 'Vault_Transfer',
                        'amount'           => $closingBalance,
                        'status'           => 'Approved',
                    ]);

                    // Jurnal Double-Entry:
                    // Debit  : Kas Utama / Brankas (Akun 102) -> Kas fisik brankas bertambah
                    // Credit : Kas Teller / Laci (Akun 101)   -> Kas fisik laci teller disetorkan
                    AccountingJournal::create([
                        'transaction_id' => $transaction->id,
                        'account_code'   => '102', // Kas Utama / Brankas
                        'position'       => 'Debit',
                        'amount'         => $closingBalance,
                    ]);

                    AccountingJournal::create([
                        'transaction_id' => $transaction->id,
                        'account_code'   => '101', // Kas Teller
                        'position'       => 'Credit',
                        'amount'         => $closingBalance,
                    ]);
                }
            });

            return redirect()->route('admin.shifts.index')
                ->with('success', 'Laporan kas teller disetujui. Dana fisik berhasil dipindahkan ke Brankas.');
        } catch (\Exception $e) {
            return redirect()->route('admin.shifts.index')
                ->with('error', 'Gagal memproses persetujuan shift: ' . $e->getMessage());
        }
    }
}
