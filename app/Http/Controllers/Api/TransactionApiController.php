<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use App\Http\Resources\SaverResource;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TransactionApiController extends Controller
{
    public function searchSaver(Request $request)
    {
        $query = trim($request->input('q') ?? $request->input('account_number') ?? $request->input('student_id') ?? $request->input('nis') ?? '');

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter pencarian tidak boleh kosong',
                'data'    => null
            ], 422);
        }
        
        $saver = Saver::where('account_number', $query)
            ->orWhere('student_id', $query)
            ->first();

        if (!$saver) {
            // Cek calon nasabah di tabel members jika ada
            $member = \App\Models\Member::where('student_id', $query)->first();
            if ($member) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data calon nasabah (Anggota) ditemukan',
                    'data'    => [
                        'id'              => null,
                        'account_number'  => $member->student_id,
                        'student_id'      => $member->student_id,
                        'nis'             => $member->student_id,
                        'student_name'    => $member->name,
                        'name'            => $member->name,
                        'grade_class'     => $member->graduation_year ? ('Angkatan ' . $member->graduation_year) : '-',
                        'saldo'           => 0,
                        'balance'         => 0,
                        'current_balance' => 0,
                        'formatted_saldo' => 'Rp 0 (Rekening Baru)',
                        'is_member_only'  => true,
                        'status'          => $member->status,
                    ]
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Data nasabah tidak ditemukan',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data nasabah berhasil ditemukan',
            'data'    => new SaverResource($saver)
        ], 200);
    }

    public function deposit(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|exists:savers,account_number',
            'amount' => 'required|numeric|min:10000',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $saver = Saver::where('account_number', $request->account_number)->lockForUpdate()->firstOrFail();

                // Validasi status keaktifan rekening
                $statusLower = strtolower(trim((string) ($saver->status ?? '')));
                if ($statusLower === 'blocked' || $statusLower === 'blokir') {
                    throw new \Exception('Transaksi Ditolak: Rekening nasabah sedang DIBLOKIR oleh Administrator.', 403);
                }
                if ($statusLower !== 'active' && $statusLower !== 'aktif') {
                    throw new \Exception('Transaksi Ditolak: Rekening nasabah berstatus Belum Aktif. Silakan lakukan aktivasi terlebih dahulu di loket Teller.', 403);
                }

                $transaction = Transaction::create([
                    'saver_id' => $saver->id,
                    'user_id' => $request->user()->id,
                    'transaction_date' => now(),
                    'transaction_type' => 'Deposit',
                    'amount' => $request->amount,
                ]);
                $saver->saldo += $request->amount;
                $saver->save();

                DB::table('accounting_journals')->insert([
                [
                    'transaction_id' => $transaction->id,
                    'account_code' => '101',
                    'position' => 'Debit',
                    'amount' => $request->amount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'transaction_id' => $transaction->id,
                    'account_code' => '201',
                    'position' => 'Credit',
                    'amount' => $request->amount,
                    'created_at' => now(),
                    'updated_at' => now(), 
                ]
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Cash deposit processed successfully and journal recorded'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'System error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function withdrawal(Request $request)
    {
        $request->validate([
            'account_number' => 'required|string|exists:savers,account_number',
            'amount' => 'required|numeric|min:1000',
            'security_pin' => 'required|digits:6',
        ], [
            'amount.min' => 'Nominal penarikan tunai minimal Rp 1.000.',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $saver = Saver::where('account_number', $request->account_number)->lockForUpdate()->firstOrFail();

                // Validasi status keaktifan rekening
                $statusLower = strtolower(trim((string) ($saver->status ?? '')));
                if ($statusLower === 'blocked' || $statusLower === 'blokir') {
                    throw new \Exception('Penarikan Ditolak: Rekening nasabah sedang DIBLOKIR oleh Administrator.', 403);
                }
                if ($statusLower !== 'active' && $statusLower !== 'aktif') {
                    throw new \Exception('Penarikan Ditolak: Rekening nasabah berstatus Belum Aktif. Transaksi tidak dapat diproses.', 403);
                }

                if (!Hash::check($request->security_pin, $saver->security_pin)) {
                    throw new \Exception('PIN keamanan 6-digit salah.', 403);
                }

                $nominalTarik = (float) $request->amount;

                // 1. Cek saldo mengendap minimal Rp 10.000
                $sisaSaldo = (float) $saver->saldo - $nominalTarik;
                if ($sisaSaldo < 10000) {
                    $maxBisaDitarik = max(0, (float) $saver->saldo - 10000);
                    throw new \Exception('Penarikan Ditolak: Saldo minimal mengendap adalah Rp 10.000. Maksimal yang dapat ditarik: Rp ' . number_format($maxBisaDitarik, 0, ',', '.'), 400);
                }

                // 2. Cek ketersediaan uang kas fisik di laci kasir bersama
                $activeShift = \App\Models\TellerShift::getActiveOpenShift();
                if ($activeShift) {
                    $activeShift->attachTeller($request->user()->id);
                    $kasLaciTersedia = $activeShift->calculateCurrentDrawerCash();
                    if ($kasLaciTersedia <= 0 || $nominalTarik > $kasLaciTersedia) {
                        throw new \Exception('Penarikan Ditolak: Uang kas fisik di laci tidak mencukupi (Tersedia: Rp ' . number_format($kasLaciTersedia, 0, ',', '.') . ').', 400);
                    }
                }

                // 3. Aturan Batas Penarikan: Reguler (<= 1jt) vs Pengajuan Penarikan (> 1jt)
                $withdrawalReq = null;
                if ($nominalTarik > 1000000) {
                    $withdrawalReq = \App\Models\WithdrawalRequest::where('saver_id', $saver->id)
                        ->where('status', 'approved')
                        ->where('amount', '>=', $nominalTarik)
                        ->latest()
                        ->first();
                    if (!$withdrawalReq) {
                        throw new \Exception('Penarikan Ditolak: Penarikan di atas Rp 1.000.000 wajib membuat Pengajuan Penarikan minimal H-1 dan telah disetujui.', 400);
                    }
                } else {
                    $maxRegulerPerHari = 1000000;
                    $todayWithdrawn = (float) Transaction::where('saver_id', $saver->id)
                        ->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])
                        ->whereDate('created_at', today())
                        ->where('status', '!=', 'Failed')
                        ->where('amount', '<=', $maxRegulerPerHari)
                        ->sum('amount');
                    if (($todayWithdrawn + $nominalTarik) > $maxRegulerPerHari) {
                        $sisaKuota = max(0, $maxRegulerPerHari - $todayWithdrawn);
                        throw new \Exception('Penarikan Ditolak: Batas penarikan reguler adalah Rp 1.000.000 per hari. Sisa kuota hari ini: Rp ' . number_format($sisaKuota, 0, ',', '.'), 400);
                    }
                }

                $transaction = Transaction::create([
                    'saver_id' => $saver->id,
                    'user_id' => $request->user()->id,
                    'transaction_date' => now(),
                    'transaction_type' => 'Withdrawal',
                    'amount' => $nominalTarik,
                ]);

                if ($withdrawalReq) {
                    $withdrawalReq->status = 'disbursed';
                    $withdrawalReq->transaction_id = $transaction->id;
                    $withdrawalReq->save();
                }

                $saver->saldo -= $nominalTarik;
                $saver->save();

                DB::table('accounting_journals')->insert([
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '201', // Tabungan
                        'position'       => 'Debit',
                        'amount'         => $request->amount,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ],
                    [
                        'transaction_id' => $transaction->id,
                        'account_code'   => '101', // Kas
                        'position'       => 'Credit',
                        'amount'         => $request->amount,
                        'created_at'     => now(),
                        'updated_at'     => now(),
                    ]
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Cash withdrawal processed successfully'
            ], 200);
        } catch (\Exception $e) {
            $statusCode = in_array($e->getCode(), [400,403]) ? $e->getCode() : 500;

            return response()->json([
                'success' => false,
                'message' => in_array($e->getCode(), [400,403]) ? $e->getMessage() : 'System error occured : ' . $e->getMessage()
            ], $statusCode);
        }
    }
}

        