<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOtpMail;

class WebForgotPasswordController extends Controller
{
    /**
     * Tampilkan form input email untuk reset password.
     */
    public function showEmailForm()
    {
        return view('auth.forgot-password.email');
    }

    /**
     * Validasi email, generate OTP 6-digit, simpan ke session, dan kirim via Email.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->with('error', 'Gagal mengirim OTP: Email tidak terdaftar di sistem.')
                ->withErrors(['email' => 'Email tidak terdaftar di sistem.'])
                ->withInput();
        }

        // Generate 6-digit random OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan data identitas & OTP ke session dengan batas waktu 2 menit
        session([
            'reset_user_id'        => $user->id,
            'reset_otp'            => $otp,
            'reset_otp_expires_at' => now()->addMinutes(2)->timestamp,
            'reset_role'           => $user->role,
            'otp_verified'         => false,
        ]);

        // Kirim OTP via Mailable SendOtpMail dengan template Blade resmi
        $officerName = $user->officer_name ?? $user->username;
        $roleName    = $user->role ?? 'Pegawai';

        $emailSent = false;
        try {
            Mail::to($user->email)->send(new SendOtpMail($otp, $officerName, $roleName));
            $emailSent = true;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gagal mengirim email OTP: ' . $e->getMessage());

            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Email belum terkirim ({$e->getMessage()}). Untuk melanjutkan pengujian/demo, gunakan kode OTP: {$otp}");
            } else {
                return back()
                    ->withErrors(['email' => 'Gagal mengirim email OTP: ' . $e->getMessage() . '. Pastikan konfigurasi SMTP email sudah benar dan valid.'])
                    ->withInput();
            }
        }

        return redirect()->route('password.otp.form')
            ->with('success', $emailSent 
                ? 'Kode OTP berhasil dikirimkan ke alamat email Anda.' 
                : 'Kode OTP berhasil dibuat untuk sesi ini.');
    }

    /**
     * Tampilkan form input verifikasi OTP.
     */
    public function showOtpForm()
    {
        if (!session()->has('reset_user_id')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Silakan masukkan alamat email Anda terlebih dahulu.']);
        }

        return view('auth.forgot-password.otp');
    }

    /**
     * Validasi kode OTP yang diinputkan dengan session.
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP harus berjumlah 6 digit angka.',
        ]);

        $expiresAt = session('reset_otp_expires_at');
        if (!$expiresAt || now()->timestamp > $expiresAt) {
            return back()
                ->with('error', 'Verifikasi Gagal: Kode OTP telah kedaluwarsa (batas waktu 2 menit telah berakhir). Silakan minta kode OTP baru.')
                ->withErrors(['otp' => 'Kode OTP telah kedaluwarsa (batas waktu 2 menit telah berakhir). Silakan minta kode OTP baru.'])
                ->withInput();
        }

        $storedOtp = session('reset_otp');

        if (!$storedOtp || $storedOtp !== $request->otp) {
            return back()
                ->with('error', 'Verifikasi Gagal: Kode OTP yang Anda masukkan salah atau tidak cocok! Silakan periksa kembali kode 6 digit di email Anda.')
                ->withErrors(['otp' => 'Kode OTP salah atau tidak cocok.'])
                ->withInput();
        }

        // Tandai verifikasi OTP berhasil
        session(['otp_verified' => true]);

        return redirect()->route('password.reset.form')
            ->with('success', 'Verifikasi OTP berhasil. Silakan buat kata sandi baru Anda.');
    }

    /**
     * Tampilkan form input kata sandi baru.
     */
    public function showResetForm()
    {
        if (!session('otp_verified') || !session()->has('reset_user_id')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi verifikasi tidak valid atau telah berakhir.']);
        }

        return view('auth.forgot-password.reset');
    }

    /**
     * Simpan kata sandi baru, bersihkan session reset, dan redirect ke login.
     */
    public function resetPassword(Request $request)
    {
        if (!session('otp_verified') || !session()->has('reset_user_id')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Sesi Anda telah kedaluwarsa. Silakan ulangi langkah permintaan OTP.']);
        }

        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi minimal terdiri dari 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::find(session('reset_user_id'));

        if (!$user) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Data pengguna tidak ditemukan.']);
        }

        // Update password baru yang sudah di-hash
        $user->password = Hash::make($request->password);
        $user->save();

        // Bersihkan seluruh session terkait alur reset password
        session()->forget([
            'reset_user_id',
            'reset_otp',
            'reset_role',
            'otp_verified',
        ]);

        return redirect()->route('login')
            ->with('success', 'Kata sandi Anda berhasil diperbarui. Silakan login dengan kata sandi baru.');
    }
}
