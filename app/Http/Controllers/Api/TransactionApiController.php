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

                if (!Hash::check($request->security_pin, $saver->security_pin)) {
                    throw new \Exception('Invalid security PIN', 403);
                }

                $sisaSaldo = $saver->saldo - $request->amount;
                if ($sisaSaldo < 10000) {
                    throw new \Exception('Withdrawal Rejected : Minimum balance remining must be Rp 10.000', 400);
                }

                $transaction = Transaction::create([
                    'saver_id' => $saver->id,
                    'user_id' => $request->user()->id,
                    'transaction_date' => now(),
                    'transaction_type' => 'Withdrawal',
                    'amount' => $request->amount,
                ]);

                $saver->saldo -= $request->amount;
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

        