<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Saver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MobileAuthController extends Controller
{
    /**
     * Request a 6-digit OTP after verifying student_id (NIS) and username.
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string',
            'username'   => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Cari data nasabah/santri berdasarkan NIS dan Username
        $saver = Saver::where('student_id', $request->student_id)
            ->where('username', $request->username)
            ->first();

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Data santri/siswa tidak ditemukan. Pastikan NIS dan Username sudah sesuai.',
            ], 404);
        }

        // Cek status keaktifan rekening
        $saverStatus = strtolower(trim((string) ($saver->status ?? '')));
        if ($saverStatus === 'blocked' || $saverStatus === 'blokir') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan Anda sedang DIBLOKIR. Silakan hubungi Administrator atau Teller sekolah.',
            ], 403);
        }

        if ($saverStatus !== 'active' && $saverStatus !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan belum aktif. Silakan lakukan aktivasi di loket Teller Bank Mini terlebih dahulu.',
            ], 403);
        }

        // Generate 6 digit random OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan OTP di Cache selama 5 menit
        $cacheKey = 'otp_' . $saver->student_id;
        Cache::put($cacheKey, $otp, now()->addMinutes(5));

        $targetEmail = $request->filled('email') ? strtolower(trim($request->email)) : $saver->email;
        if (!empty($targetEmail) && filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                \App\Mail\SendOtpMail::sendReliably(
                    $targetEmail,
                    $otp,
                    $saver->student_name ?? $saver->username,
                    'Nasabah',
                    'aktivasi kredensial akun mobile',
                    'Kode OTP Aktivasi Mobile - Bank Mini'
                );
            } catch (\Throwable $e) {
                Log::warning('MobileAuth OTP Email gagal dikirim: ' . $e->getMessage());
            }
        }

        // Catat log pengiriman OTP
        Log::info("Pengiriman OTP Nasabah [ID: {$saver->id}, NIS: {$saver->student_id}, Email: {$targetEmail}]: {$otp}");

        return response()->json([
            'success' => true,
            'message' => 'Kode OTP berhasil dibuat dan dikirimkan.',
        ], 200);
    }

    /**
     * Verify the OTP entered by the student.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string',
            'otp'        => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $cacheKey = 'otp_' . $request->student_id;
        $storedOtp = Cache::get($cacheKey);

        if (!$storedOtp || $storedOtp !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP tidak valid atau telah kadaluarsa.',
            ], 400);
        }

        // Hapus OTP terpakai dan beri flag verifikasi sementara (10 menit)
        Cache::forget($cacheKey);
        Cache::put('otp_verified_' . $request->student_id, true, now()->addMinutes(10));

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi OTP berhasil. Silakan lengkapi password dan PIN transaksi Anda.',
        ], 200);
    }

    /**
     * Setup Credentials: Sets up login Password and transaction PIN (6 digits).
     * Replaces the previous setupPin method.
     */
    public function setupCredentials(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|string',
            'password'   => 'required|string|min:6|confirmed', // membutuhkan field 'password_confirmation'
            'pin'        => 'required|digits:6|confirmed',      // membutuhkan field 'pin_confirmation'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $saver = Saver::where('student_id', $request->student_id)->first();

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Nasabah tidak ditemukan.',
            ], 404);
        }

        // Simpan Hash Password untuk Login & Hash PIN untuk Transaksi
        $saver->password = Hash::make($request->password);
        $saver->security_pin = Hash::make($request->pin);
        $saver->save();

        // Bersihkan cache status verifikasi OTP
        Cache::forget('otp_verified_' . $saver->student_id);

        // Terbitkan Sanctum token untuk aplikasi mobile
        $token = $saver->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Kredensial berhasil disimpan. Autentikasi berhasil.',
            'token'   => $token,
            'data'    => [
                'id'           => $saver->id,
                'student_id'   => $saver->student_id,
                'student_name' => $saver->student_name,
                'username'     => $saver->username,
                'saldo'        => $saver->saldo,
            ],
        ], 200);
    }

    /**
     * Daily Login using Username and Password.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Cari nasabah berdasarkan username
        $saver = Saver::where('username', $request->username)->first();

        // Verifikasi keberadaan user dan kecocokan password
        if (!$saver || !Hash::check($request->password, $saver->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau Password salah.',
            ], 401);
        }

        // Cek status keaktifan rekening
        $saverStatus = strtolower(trim((string) ($saver->status ?? '')));
        if ($saverStatus === 'blocked' || $saverStatus === 'blokir') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan Anda sedang DIBLOKIR. Silakan hubungi Administrator atau loket Teller sekolah.',
            ], 403);
        }

        if ($saverStatus !== 'active' && $saverStatus !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan belum aktif. Silakan lakukan aktivasi dan setoran awal di loket Teller Bank Mini terlebih dahulu.',
            ], 403);
        }

        // Generate Sanctum access token baru
        $token = $saver->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token'   => $token,
            'data'    => [
                'id'           => $saver->id,
                'student_id'   => $saver->student_id,
                'student_name' => $saver->student_name,
                'username'     => $saver->username,
                'saldo'        => $saver->saldo,
            ],
        ], 200);
    }
}
