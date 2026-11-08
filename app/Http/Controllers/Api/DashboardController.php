<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Mengambil ringkasan data dashboard untuk Mobile App (Nasabah/Saver).
     */
    public function index(Request $request)
    {
        try {
            // 1. Ambil data pengguna terautentikasi (via Sanctum)
            $authUser = $request->user() ?? auth()->user();

            if (!$authUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                ], 401);
            }

            $name = '';
            $accountNumber = '';
            $balance = 0;
            $saverId = null;

            // 2. Identifikasi identitas & saldo nasabah serta validasi status aktif
            if ($authUser instanceof Saver) {
                $statusLower = strtolower(trim((string) ($authUser->status ?? '')));
                if ($statusLower === 'blocked' || $statusLower === 'blokir') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rekening tabungan Anda sedang DIBLOKIR oleh Administrator. Silakan hubungi loket Teller sekolah.',
                    ], 403);
                }

                if ($statusLower !== 'active' && $statusLower !== 'aktif') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Rekening tabungan belum aktif. Silakan lakukan aktivasi di loket Teller Bank Mini terlebih dahulu.',
                    ], 403);
                }

                $saverId = $authUser->id;
                $name = $authUser->student_name ?? $authUser->name ?? 'Nasabah';
                $accountNumber = $authUser->account_number ?? '-';
                $balance = isset($authUser->saldo) ? (float) $authUser->saldo : null;
            } elseif ($authUser instanceof User) {
                // Jika auth menggunakan model User, cocokkan dengan profil Saver terkait
                $userStatus = strtolower(trim((string) ($authUser->status ?? '')));
                if ($userStatus === 'blokir') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Akun pengguna Anda sedang diblokir oleh Administrator.',
                    ], 403);
                }

                $saver = Saver::where('student_id', $authUser->username)
                    ->orWhere('email', $authUser->email)
                    ->first();

                if ($saver) {
                    $saverStatus = strtolower(trim((string) ($saver->status ?? '')));
                    if ($saverStatus === 'blocked' || $saverStatus === 'blokir') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Rekening tabungan Anda sedang DIBLOKIR oleh Administrator.',
                        ], 403);
                    }

                    if ($saverStatus !== 'active' && $saverStatus !== 'aktif') {
                        return response()->json([
                            'success' => false,
                            'message' => 'Rekening tabungan belum aktif.',
                        ], 403);
                    }

                    $saverId = $saver->id;
                    $name = $saver->student_name ?? $saver->name ?? $authUser->officer_name ?? $authUser->username;
                    $accountNumber = $saver->account_number ?? '-';
                    $balance = isset($saver->saldo) ? (float) $saver->saldo : null;
                } else {
                    $name = $authUser->officer_name ?? $authUser->name ?? $authUser->username;
                    $accountNumber = $authUser->account_number ?? '-';
                    $balance = isset($authUser->balance) ? (float) $authUser->balance : (isset($authUser->saldo) ? (float) $authUser->saldo : 0);
                }
            }

            // 3. Fallback: Hitung saldo dari selisih transaksi (Deposit - Withdrawal) jika kolom saldo belum tersedia
            if ($balance === null && $saverId) {
                $totalDeposit = Transaction::where('saver_id', $saverId)
                    ->whereIn('transaction_type', ['Deposit', 'setor'])
                    ->sum('amount');

                $totalWithdrawal = Transaction::where('saver_id', $saverId)
                    ->whereIn('transaction_type', ['Withdrawal', 'tarik'])
                    ->sum('amount');

                $balance = (float) ($totalDeposit - $totalWithdrawal);
            }

            $balance = (float) ($balance ?? 0);

            // 4. Ambil 5 riwayat transaksi terakhir diurutkan dari yang terbaru
            $transactionsQuery = $saverId
                ? Transaction::where('saver_id', $saverId)
                : Transaction::where('user_id', $authUser->id);

            $recentTransactions = $transactionsQuery
                ->orderBy(DB::raw('COALESCE(transaction_date, created_at)'), 'desc')
                ->limit(5)
                ->get()
                ->map(function ($tx) {
                    $rawType = strtolower($tx->transaction_type ?? $tx->type ?? '');
                    $typeLabel = 'Transaksi';

                    if (in_array($rawType, ['deposit', 'setor', 'setoran'])) {
                        $typeLabel = 'Setoran Tunai';
                    } elseif (in_array($rawType, ['withdrawal', 'tarik', 'penarikan'])) {
                        $typeLabel = 'Penarikan';
                    } elseif (in_array($rawType, ['vault_transfer', 'transfer'])) {
                        $typeLabel = 'Pemindahan Kas';
                    }

                    $txDate = $tx->transaction_date 
                        ? Carbon::parse($tx->transaction_date)->format('Y-m-d H:i:s')
                        : ($tx->created_at ? $tx->created_at->format('Y-m-d H:i:s') : null);

                    return [
                        'id'               => $tx->id,
                        'transaction_type' => $tx->transaction_type,
                        'type'             => $tx->transaction_type,
                        'type_label'       => $typeLabel,
                        'amount'           => (float) $tx->amount,
                        'transaction_date' => $txDate,
                        'status'           => $tx->status ?? 'Approved',
                    ];
                });

            // 5. Kembalikan respons JSON terstandarisasi
            return response()->json([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil',
                'data'    => [
                    'user' => [
                        'name'           => $name,
                        'account_number' => $accountNumber,
                    ],
                    'balance'             => $balance,
                    'recent_transactions' => $recentTransactions,
                ],
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memuat data dashboard: ' . $e->getMessage(),
                'data'    => null,
            ], 500);
        }
    }
}
