<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use App\Models\User;
use App\Mail\SendOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class MobileSecurityApiController extends Controller
{
    /**
     * Request OTP for Change Password, Change PIN, or Forgot Password.
     * Accessible both by authenticated mobile user (Bearer Token) or public (by identifier).
     */
    public function requestOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'sometimes|required|string',
            'type'       => 'sometimes|string|in:change_password,change_pin,forgot_password',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter validasi tidak lengkap.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // 1. Tentukan user target (dari Token Sanctum atau dari Identifier input)
        $targetSaver = null;
        $user = $request->user();
        if (!$user && $request->bearerToken()) {
            $user = auth('sanctum')->user();
            if (!$user && class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
                $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
                if ($pat) {
                    $user = $pat->tokenable;
                }
            }
        }

        if ($user) {
            if ($user instanceof Saver) {
                $targetSaver = $user;
            } else {
                $targetSaver = Saver::where('student_id', $user->student_id ?? '')
                    ->orWhere('username', $user->username)
                    ->orWhere('email', $user->email)
                    ->orWhere('account_number', $user->account_number ?? '')
                    ->first();
            }
        }

        if (!$targetSaver && $request->filled('identifier')) {
            $id = trim($request->identifier);
            $targetSaver = Saver::where('account_number', $id)
                ->orWhere('student_id', $id)
                ->orWhere('username', $id)
                ->orWhere('email', $id)
                ->first();

            if (!$targetSaver) {
                $foundUser = User::where('username', $id)
                    ->orWhere('email', $id)
                    ->first();
                if ($foundUser) {
                    $targetSaver = Saver::where('student_id', $foundUser->student_id ?? '')
                        ->orWhere('username', $foundUser->username)
                        ->orWhere('email', $foundUser->email)
                        ->first();
                }
            }
        }

        if (!$targetSaver) {
            return response()->json([
                'success' => false,
                'message' => 'Data akun nasabah tidak ditemukan. Pastikan Username, NIS, atau No. Rekening benar.',
            ], 404);
        }

        // 2. Ambil email nasabah (dari tabel savers atau tabel users terkait)
        $email = $targetSaver->email;
        if (empty($email)) {
            $email = User::where('username', $targetSaver->username)
                ->orWhere('officer_name', $targetSaver->student_name)
                ->value('email');
        }

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Gunakan fallback email jika nasabah belum mendaftarkan email di sistem
            $cleanUser = preg_replace('/[^a-zA-Z0-9]/', '', $targetSaver->username ?? $targetSaver->student_id ?? $targetSaver->account_number ?? 'nasabah');
            $email = strtolower($cleanUser) . '@bankmini.sch.id';
        }

        // 3. Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'mobile_otp_' . md5(strtolower($email));

        // Simpan OTP di cache selama 10 menit
        cache()->put($cacheKey, [
            'otp'        => $otp,
            'saver_id'   => $targetSaver->id,
            'email'      => $email,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        // 4. Kirim Email Riil via SMTP
        $studentName = $targetSaver->student_name ?? $targetSaver->username;
        $emailSent = false;
        try {
            Mail::to($email)->send(new SendOtpMail($otp, $studentName, 'Nasabah'));
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Mobile OTP Email gagal dikirim: ' . $e->getMessage());
        }

        // Masking email untuk keamanan (contoh: f***e@gmail.com)
        $parts = explode('@', $email);
        $namePart = $parts[0];
        $domainPart = $parts[1] ?? '';
        $maskedName = strlen($namePart) > 2
            ? substr($namePart, 0, 1) . str_repeat('*', max(strlen($namePart) - 2, 3)) . substr($namePart, -1)
            : $namePart . '***';
        $maskedEmail = $maskedName . '@' . $domainPart;

        return response()->json([
            'success'      => true,
            'message'      => $emailSent 
                ? 'Kode OTP berhasil dikirimkan ke email ' . $maskedEmail 
                : 'Kode OTP berhasil diproses.',
            'masked_email' => $maskedEmail,
            'email'        => $email,
            // Mode developer: sertakan dev_otp agar mudah diuji
            'dev_otp'      => config('app.debug') ? $otp : null,
            'expires_in'   => 600,
        ], 200);
    }

    /**
     * Verify the 6-digit OTP entered by the user.
     * Returns a temporary one-time reset_token valid for 15 minutes.
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required_without:identifier|email',
            'identifier' => 'sometimes|string',
            'otp'   => 'required|digits:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Format kode OTP harus 6 digit angka.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        if (empty($email) && $request->filled('identifier')) {
            $id = trim($request->identifier);
            $saver = Saver::where('username', $id)
                ->orWhere('student_id', $id)
                ->orWhere('account_number', $id)
                ->orWhere('email', $id)
                ->first();
            $email = $saver?->email ?? User::where('username', $saver?->username)->value('email');
        }

        if (empty($email)) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas email tidak valid.',
            ], 400);
        }

        $cacheKey = 'mobile_otp_' . md5(strtolower($email));
        $cached = cache()->get($cacheKey);

        $valid = false;
        if ($cached && isset($cached['otp']) && $cached['otp'] === $request->otp) {
            $valid = true;
        }

        if (!$valid) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau telah kedaluwarsa. Silakan periksa kembali email Anda.',
            ], 400);
        }

        // Hapus OTP terpakai
        cache()->forget($cacheKey);

        // Terbitkan reset_token berlaku 15 menit
        $resetToken = 'rst_mob_' . bin2hex(random_bytes(24));
        cache()->put('mobile_reset_' . $resetToken, [
            'saver_id' => $cached['saver_id'],
            'email'    => $email,
        ], now()->addMinutes(15));

        return response()->json([
            'success'     => true,
            'message'     => 'Verifikasi OTP berhasil. Silakan buat kata sandi atau PIN baru Anda.',
            'reset_token' => $resetToken,
        ], 200);
    }

    /**
     * Change or Reset Password (Requires reset_token, password, password_confirmation).
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reset_token'           => 'required|string',
            'password'              => 'required|string|min:6|confirmed', // membutuhkan field 'password_confirmation'
            'password_confirmation' => 'required|string',
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok. Pastikan kedua input kata sandi sama persis.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $cached = cache()->get('mobile_reset_' . $request->reset_token);
        if (!$cached) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi telah kedaluwarsa. Silakan lakukan permintaan OTP ulang.',
            ], 403);
        }

        $saver = Saver::find($cached['saver_id']);
        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Data nasabah tidak ditemukan.',
            ], 404);
        }

        // Update password di tabel savers dan users (sinkronisasi)
        $hashed = Hash::make($request->password);
        $saver->password = $hashed;
        $saver->save();

        User::where('username', $saver->username)
            ->orWhere('email', $cached['email'])
            ->update(['password' => $hashed]);

        // Cabut token reset
        cache()->forget('mobile_reset_' . $request->reset_token);

        return response()->json([
            'success' => true,
            'message' => 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru Anda.',
        ], 200);
    }

    /**
     * Change or Reset Security PIN (Requires reset_token, pin, pin_confirmation).
     */
    public function resetPin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reset_token'      => 'required|string',
            'pin'              => 'required|digits:6|confirmed', // membutuhkan field 'pin_confirmation'
            'pin_confirmation' => 'required|digits:6',
        ], [
            'pin.required'  => 'PIN transaksi baru wajib diisi.',
            'pin.digits'    => 'PIN transaksi harus berupa 6 digit angka.',
            'pin.confirmed' => 'Konfirmasi PIN transaksi baru tidak cocok. Pastikan kedua input PIN sama persis.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $cached = cache()->get('mobile_reset_' . $request->reset_token);
        if (!$cached) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi telah kedaluwarsa. Silakan lakukan permintaan OTP ulang.',
            ], 403);
        }

        $saver = Saver::find($cached['saver_id']);
        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Data nasabah tidak ditemukan.',
            ], 404);
        }

        $pin = $request->pin;

        // Cek PIN lemah / berulang
        $identical = ['000000','111111','222222','333333','444444','555555','666666','777777','888888','999999'];
        $sequential = ['012345','123456','234567','345678','456789','567890','987654','876543','765432','654321','543210'];
        if (in_array($pin, $identical) || in_array($pin, $sequential)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN terlalu mudah ditebak. Hindari kombinasi angka berulang atau berurutan.',
            ], 422);
        }

        $saver->security_pin = Hash::make($pin);
        $saver->save();

        // Cabut token reset
        cache()->forget('mobile_reset_' . $request->reset_token);

        return response()->json([
            'success' => true,
            'message' => 'PIN transaksi 6 digit Anda berhasil diperbarui!',
        ], 200);
    }

    /**
     * Permintaan OTP untuk Ubah Email Akun Nasabah (Wajib Kata Sandi Saat Ini).
     */
    public function requestChangeEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_email' => 'required|email',
            'password'  => 'required|string',
        ], [
            'new_email.required' => 'Alamat email baru wajib diisi.',
            'new_email.email'    => 'Format alamat email baru tidak valid.',
            'password.required'  => 'Kata sandi saat ini wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Cari user yang terotentikasi
        $targetSaver = null;
        $user = $request->user();
        if (!$user && $request->bearerToken() && class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
            $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($pat) {
                $user = $pat->tokenable;
            }
        }

        if ($user) {
            if ($user instanceof Saver) {
                $targetSaver = $user;
            } else {
                $targetSaver = Saver::where('student_id', $user->student_id ?? '')
                    ->orWhere('username', $user->username ?? '')
                    ->orWhere('email', $user->email ?? '')
                    ->orWhere('account_number', $user->account_number ?? '')
                    ->first();
            }
        }

        if (!$targetSaver) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid. Silakan masuk kembali.',
            ], 401);
        }

        // Validasi kata sandi saat ini
        $passwordValid = false;
        if (!empty($targetSaver->password) && Hash::check($request->password, $targetSaver->password)) {
            $passwordValid = true;
        }

        // Fallback: periksa password di tabel User terkait jika saver->password belum cocok
        if (!$passwordValid) {
            $linkedUser = User::where('username', $targetSaver->username)
                ->orWhere('student_id', $targetSaver->student_id)
                ->first();
            if ($linkedUser && !empty($linkedUser->password) && Hash::check($request->password, $linkedUser->password)) {
                $passwordValid = true;
            }
        }

        // Fallback: jika belum pernah set password (misal akun baru), cek PIN transaksi
        if (!$passwordValid && !empty($targetSaver->security_pin) && Hash::check($request->password, $targetSaver->security_pin)) {
            $passwordValid = true;
        }

        if (!$passwordValid) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi saat ini salah. Pastikan kata sandi yang Anda masukkan benar.',
            ], 422);
        }

        $newEmail = strtolower(trim($request->new_email));

        // Cek apakah email baru sama dengan email lama
        if (strtolower(trim($targetSaver->email ?? '')) === $newEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat email baru tidak boleh sama dengan email akun saat ini.',
            ], 422);
        }

        // Generate OTP 6 digit
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'mobile_change_email_otp_' . $targetSaver->id;

        // Simpan ke cache selama 10 menit
        cache()->put($cacheKey, [
            'otp'        => $otp,
            'saver_id'   => $targetSaver->id,
            'new_email'  => $newEmail,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        // Kirim email OTP ke alamat email BARU
        $emailSent = false;
        try {
            Mail::to($newEmail)->send(new SendOtpMail($otp, $targetSaver->student_name ?? 'Nasabah', 'Verifikasi Ganti Email'));
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('OTP Ganti Email gagal dikirim: ' . $e->getMessage());
        }

        return response()->json([
            'success'    => true,
            'message'    => 'Kode OTP verifikasi telah dikirim ke ' . $newEmail . '. Silakan periksa kotak masuk email Anda.',
            'new_email'  => $newEmail,
            'dev_otp'    => config('app.debug') ? $otp : null,
            'expires_in' => 600,
        ], 200);
    }

    /**
     * Verifikasi OTP dan perbarui email akun nasabah.
     */
    public function verifyChangeEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Kode OTP verifikasi wajib diisi.',
            'otp.digits'   => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $targetSaver = null;
        $user = $request->user();
        if (!$user && $request->bearerToken() && class_exists(\Laravel\Sanctum\PersonalAccessToken::class)) {
            $pat = \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken());
            if ($pat) {
                $user = $pat->tokenable;
            }
        }

        if ($user) {
            if ($user instanceof Saver) {
                $targetSaver = $user;
            } else {
                $targetSaver = Saver::where('student_id', $user->student_id ?? '')
                    ->orWhere('username', $user->username ?? '')
                    ->orWhere('email', $user->email ?? '')
                    ->orWhere('account_number', $user->account_number ?? '')
                    ->first();
            }
        }

        if (!$targetSaver) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid. Silakan masuk kembali.',
            ], 401);
        }

        $cacheKey = 'mobile_change_email_otp_' . $targetSaver->id;
        $cached = cache()->get($cacheKey);

        if (!$cached || !isset($cached['otp'])) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi OTP telah kedaluwarsa atau belum diminta. Silakan lakukan permintaan OTP ulang.',
            ], 400);
        }

        if ($cached['otp'] !== $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah. Silakan periksa kembali email Anda.',
            ], 400);
        }

        $newEmail = $cached['new_email'];

        // Perbarui email di tabel savers
        $targetSaver->email = $newEmail;
        $targetSaver->save();

        // Sinkronisasi dengan tabel users jika ada
        User::where('username', $targetSaver->username)
            ->orWhere('student_id', $targetSaver->student_id)
            ->update(['email' => $newEmail]);

        // Hapus cache OTP terpakai
        cache()->forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Email akun berhasil diubah dan terverifikasi menjadi ' . $newEmail,
            'data'    => [
                'email' => $newEmail,
            ],
        ], 200);
    }
}

