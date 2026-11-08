<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Otentikasi dan login pengguna Mobile App (Nasabah/Saver).
     */
    public function login(Request $request)
    {
        // 1. Validasi input
        $validator = Validator::make($request->all(), [
            'username'       => 'required_without_all:account_number,email,student_id|string',
            'account_number' => 'sometimes|string',
            'email'          => 'sometimes|string',
            'student_id'     => 'sometimes|string',
            'password'       => 'required_without:security_pin|string',
            'security_pin'   => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal: Harap masukkan username/nomor rekening dan kata sandi.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $identifier = $request->input('username') 
            ?? $request->input('account_number') 
            ?? $request->input('email') 
            ?? $request->input('student_id');

        $secret = $request->input('password') ?? $request->input('security_pin');

        // 2. Cari nasabah di model Saver (atau User)
        $user = Saver::where('username', $identifier)
            ->orWhere('account_number', $identifier)
            ->orWhere('email', $identifier)
            ->orWhere('student_id', $identifier)
            ->first();

        $saverModel = $user instanceof Saver ? $user : null;

        if (!$user) {
            $user = User::where('username', $identifier)
                ->orWhere('email', $identifier)
                ->first();

            if ($user) {
                $saverModel = Saver::where('user_id', $user->id)
                    ->orWhere('username', $user->username)
                    ->orWhere('student_id', str_replace('siswa_', '', $user->username))
                    ->orWhere('email', $user->email)
                    ->first();
            }
        }

        // 3. Verifikasi apakah akun ditemukan
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial tidak valid',
            ], 401);
        }

        // 4. Verifikasi kata sandi / PIN menggunakan Hash::check()
        $passwordValid = false;

        if (!empty($user->password) && Hash::check($secret, $user->password)) {
            $passwordValid = true;
        } elseif ($user instanceof Saver && !empty($user->security_pin) && Hash::check($secret, $user->security_pin)) {
            $passwordValid = true;
        } elseif ($saverModel && !empty($saverModel->security_pin) && Hash::check($secret, $saverModel->security_pin)) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial tidak valid',
            ], 401);
        }

        // 5. Validasi Status Rekening Nasabah (Wajib Aktif)
        // Jika rekening berstatus belum aktif, nonaktif, atau diblokir, mutlak DITOLAK masuk
        $effectiveStatus = 'active';
        if ($saverModel) {
            $effectiveStatus = strtolower(trim((string) ($saverModel->status ?? '')));
        } elseif ($user instanceof Saver) {
            $effectiveStatus = strtolower(trim((string) ($user->status ?? '')));
        }

        if ($effectiveStatus === 'blocked' || $effectiveStatus === 'blokir' || ($user instanceof User && strtolower(trim((string) ($user->status ?? ''))) === 'blokir')) {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan Anda sedang DIBLOKIR oleh Administrator. Silakan hubungi loket Teller atau Administrator sekolah.',
            ], 403);
        }

        if ($effectiveStatus !== 'active' && $effectiveStatus !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan belum aktif. Silakan lakukan aktivasi dan setoran awal di loket Teller Bank Mini terlebih dahulu.',
            ], 403);
        }

        // 5. Generate Sanctum token
        $token = $user->createToken('MobileAppToken')->plainTextToken;

        // Siapkan data dasar user
        $userName = $user->student_name ?? $user->name ?? $user->officer_name ?? $user->username;
        $accountNumber = $user->account_number ?? null;

        // 6. Kembalikan respons JSON terstandarisasi
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'token'   => $token,
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'             => $user->id,
                    'name'           => $userName,
                    'username'       => $user->username,
                    'account_number' => $accountNumber,
                    'student_id'     => $user->student_id ?? null,
                    'email'          => $user->email ?? null,
                    'status'         => $saverModel?->status ?? ($user instanceof Saver ? $user->status : ($user->status ?? 'active')),
                    'saldo'          => isset($user->saldo) ? (float) $user->saldo : null,
                ],
                'id'             => $user->id,
                'name'           => $userName,
                'account_number' => $accountNumber,
                'student_id'     => $user->student_id ?? null,
                'status'         => $saverModel?->status ?? ($user instanceof Saver ? $user->status : ($user->status ?? 'active')),
            ],
        ], 200);
    }

    /**
     * Logout pengguna dengan mencabut access token saat ini.
     */
    public function logout(Request $request)
    {
        if ($request->user() && $request->user()->currentAccessToken()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout',
        ], 200);
    }
}
