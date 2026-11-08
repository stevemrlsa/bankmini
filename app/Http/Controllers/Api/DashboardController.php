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

            // 2. Identifikasi identitas & saldo nasabah
            if ($authUser instanceof Saver) {
                $saverId = $authUser->id;
                $name = $authUser->student_name ?? $authUser->name ?? 'Nasabah';
                $accountNumber = $authUser->account_number ?? '-';
                $balance = isset($authUser->saldo) ? (float) $authUser->saldo : null;
            } elseif ($authUser instanceof User) {
                // Jika auth menggunakan model User, cocokkan dengan profil Saver terkait
                $saver = Saver::where('student_id', $authUser->username)
                    ->orWhere('email', $authUser->email)
                    ->first();

                if ($saver) {
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
                    $isDeposit = in_array($rawType, ['deposit', 'setor', 'setoran', 'credit']);
                    $typeLabel = 'Transaksi';

                    if ($isDeposit) {
                        $typeLabel = 'Setoran Tunai';
                    } elseif (in_array($rawType, ['withdrawal', 'tarik', 'penarikan', 'debit'])) {
                        $typeLabel = 'Penarikan Tunai';
                    } elseif (in_array($rawType, ['vault_transfer', 'transfer'])) {
                        $typeLabel = 'Pemindahan Kas';
                    }

                    $rawDate = $tx->transaction_date ?? $tx->created_at;
                    $carbonDate = $rawDate ? Carbon::parse($rawDate)->setTimezone('Asia/Jakarta') : now('Asia/Jakarta');

                    return [
                        'id'               => $tx->id,
                        'transaction_type' => $tx->transaction_type ?? ($isDeposit ? 'Deposit' : 'Withdrawal'),
                        'type'             => $isDeposit ? 'setor' : 'tarik',
                        'type_label'       => $typeLabel,
                        'is_credit'        => $isDeposit,
                        'amount'           => (float) $tx->amount,
                        'jumlah'           => (float) $tx->amount,
                        'transaction_date' => $carbonDate->format('Y-m-d H:i:s'),
                        'date'             => $carbonDate->format('Y-m-d H:i:s'),
                        'tanggal'          => $carbonDate->format('d M Y, H:i') . ' WIB',
                        'tanggal_group'    => $carbonDate->format('d M Y'),
                        'description'      => $tx->description ?? ($isDeposit ? 'Setoran Tabungan' : 'Penarikan Tunai'),
                        'keterangan'       => $tx->description ?? ($isDeposit ? 'Setoran Tabungan' : 'Penarikan Tunai'),
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
