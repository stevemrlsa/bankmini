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

        // Cek status keaktifan rekening nasabah
        $saverStatus = strtolower(trim((string) ($targetSaver->status ?? '')));
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
     * Request OTP to change email address for Mobile app.
     * Validates account status, verifies current password/PIN, and sends OTP to the new email.
     */
    public function requestChangeEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_email'  => 'required|email|max:255',
            'password'   => 'required|string',
            'identifier' => 'sometimes|string',
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

        // Cari data nasabah dari session Sanctum atau identifier
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
                'message' => 'Data akun nasabah tidak ditemukan.',
            ], 404);
        }

        // Cek status keaktifan rekening nasabah
        $saverStatus = strtolower(trim((string) ($targetSaver->status ?? '')));
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

        // Cek apakah email baru sudah digunakan oleh akun nasabah lain
        $newEmail = strtolower(trim($request->new_email));
        $emailExists = Saver::where('email', $newEmail)
            ->where('id', '!=', $targetSaver->id)
            ->exists();
        if ($emailExists) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat email tersebut sudah terdaftar pada akun nasabah lain.',
            ], 422);
        }

        // Verifikasi kata sandi atau PIN saat ini
        $secret = $request->password;
        $isValidAuth = false;
        if (!empty($targetSaver->password) && Hash::check($secret, $targetSaver->password)) {
            $isValidAuth = true;
        } elseif (!empty($targetSaver->security_pin) && Hash::check($secret, $targetSaver->security_pin)) {
            $isValidAuth = true;
        }

        if (!$isValidAuth) {
            return response()->json([
                'success' => false,
                'message' => 'Kata sandi akun yang Anda masukkan salah.',
            ], 401);
        }

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $cacheKey = 'mobile_change_email_' . md5($newEmail);

        cache()->put($cacheKey, [
            'otp'        => $otp,
            'saver_id'   => $targetSaver->id,
            'new_email'  => $newEmail,
            'expires_at' => now()->addMinutes(10)->timestamp,
        ], now()->addMinutes(10));

        // Kirim email OTP ke email baru
        $studentName = $targetSaver->student_name ?? $targetSaver->username;
        $emailSent = false;
        try {
            Mail::to($newEmail)->send(new SendOtpMail(
                $otp,
                $studentName,
                'Nasabah',
                'memverifikasi perubahan alamat email akun Bank Mini',
                'Kode OTP Verifikasi Email Baru - Bank Mini'
            ));
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Mobile Change Email OTP gagal dikirim: ' . $e->getMessage());
        }

        // Masking email baru
        $parts = explode('@', $newEmail);
        $namePart = $parts[0];
        $domainPart = $parts[1] ?? '';
        $maskedName = strlen($namePart) > 2
            ? substr($namePart, 0, 1) . str_repeat('*', max(strlen($namePart) - 2, 3)) . substr($namePart, -1)
            : $namePart . '***';
        $maskedEmail = $maskedName . '@' . $domainPart;

        return response()->json([
            'success'      => true,
            'message'      => $emailSent
                ? 'Kode OTP verifikasi telah dikirimkan ke email ' . $maskedEmail
                : 'Kode OTP berhasil diproses.',
            'masked_email' => $maskedEmail,
            'email'        => $newEmail,
            'dev_otp'      => config('app.debug') ? $otp : null,
            'expires_in'   => 600,
        ], 200);
    }

    /**
     * Verify OTP and update email address.
     */
    public function verifyChangeEmailOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'new_email'  => 'required|email',
            'otp'        => 'required|digits:6',
            'identifier' => 'sometimes|string',
        ], [
            'new_email.required' => 'Alamat email baru wajib diisi.',
            'otp.required'       => 'Kode OTP wajib diisi.',
            'otp.digits'         => 'Kode OTP harus berupa 6 digit angka.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $newEmail = strtolower(trim($request->new_email));
        $cacheKey = 'mobile_change_email_' . md5($newEmail);
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

        $saver = Saver::find($cached['saver_id']);
        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Data nasabah tidak ditemukan.',
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
                'message' => 'Rekening tabungan belum aktif.',
            ], 403);
        }

        // Update email di savers dan users
        $saver->email = $newEmail;
        $saver->save();

        User::where('username', $saver->username)
            ->orWhere('username', 'siswa_' . $saver->student_id)
            ->update(['email' => $newEmail]);

        cache()->forget($cacheKey);

        return response()->json([
            'success' => true,
            'message' => 'Alamat email akun Anda berhasil diperbarui menjadi ' . $newEmail . '!',
            'email'   => $newEmail,
        ], 200);
    }
}

