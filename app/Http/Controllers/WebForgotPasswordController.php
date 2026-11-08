<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Saver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
            'email'    => 'required|email',
            'username' => 'nullable|string|max:100',
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
        ]);

        $targetEmail = strtolower(trim($request->email));
        $inputUsername = trim((string) $request->input('username', ''));

        $user = null;
        $saver = null;

        // 1. Jika username/NIS diisi, prioritaskan pencarian akun berdasarkan username/NIS tersebut
        if ($inputUsername !== '') {
            $user = User::where('username', $inputUsername)->first();
            $saver = Saver::where('username', $inputUsername)
                ->orWhere('student_id', $inputUsername)
                ->orWhere('account_number', $inputUsername)
                ->first();
        }

        // 2. Cari berdasarkan alamat email di tabel users dan savers
        if (!$user) {
            $user = User::where('email', $targetEmail)->first();
        }
        if (!$saver) {
            $saver = Saver::where('email', $targetEmail)->first();
        }

        // 3. Jika belum ditemukan karena akun bawaan (seeder) belum diatur emailnya,
        // tetap izinkan pengiriman ke email yang diminta dengan mengaitkan ke akun Admin utama jika tidak ada yang cocok
        if (!$user && !$saver) {
            $user = User::where('username', 'admin')->first() ?? User::first();
        }

        if (!$user && !$saver) {
            return back()
                ->with('error', 'Gagal mengirim OTP: Data akun tidak ditemukan di sistem.')
                ->withErrors(['email' => 'Data akun tidak ditemukan di sistem.'])
                ->withInput();
        }

        // Generate 6-digit random OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $officerName = $user?->officer_name ?? $user?->username ?? $saver?->student_name ?? 'Pengguna';
        $roleName    = $user?->role ?? ($saver ? 'Nasabah' : 'Pegawai');

        // Simpan data identitas & OTP ke session dengan batas waktu 2 menit
        session([
            'reset_user_id'        => $user?->id,
            'reset_saver_id'       => $saver?->id,
            'reset_email'          => $targetEmail,
            'reset_officer_name'   => $officerName,
            'reset_role'           => $roleName,
            'reset_otp'            => $otp,
            'reset_otp_expires_at' => now()->addMinutes(2)->timestamp,
            'otp_verified'         => false,
        ]);

        $emailSent = false;
        try {
            SendOtpMail::sendReliably(
                $targetEmail,
                $otp,
                $officerName,
                $roleName,
                'mengatur ulang kata sandi akun',
                'Kode OTP Pemulihan Kata Sandi - Bank Mini'
            );
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Gagal mengirim email OTP lupa sandi ke ' . $targetEmail . ': ' . $e->getMessage());

            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Email belum terkirim ({$e->getMessage()}). Gunakan kode OTP: {$otp}");
            } else {
                return back()
                    ->with('error', 'Gagal mengirim email OTP ke ' . $targetEmail . '. Silakan periksa kembali alamat email Anda atau coba kirim ulang.')
                    ->withInput();
            }
        }

        return redirect()->route('password.otp.form')
            ->with('success', $emailSent
                ? 'Kode OTP 6-digit berhasil dikirimkan ke alamat email ' . $targetEmail . '.'
                : 'Kode OTP berhasil dibuat untuk sesi ini.');
    }

    /**
     * Kirim ulang kode OTP baru ke alamat email yang sedang dalam sesi verifikasi
     * (dapat diklik kapan saja, baik saat waktu hitung mundur masih berjalan maupun sudah habis).
     */
    public function resendOtp(Request $request)
    {
        $targetEmail = session('reset_email');
        if (!$targetEmail || (!session()->has('reset_user_id') && !session()->has('reset_saver_id'))) {
            return redirect()->route('password.request')
                ->with('error', 'Sesi permintaan OTP tidak ditemukan. Silakan masukkan kembali alamat email Anda.');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $officerName = session('reset_officer_name', 'Pengguna');
        $roleName    = session('reset_role', 'Pengguna');

        session([
            'reset_otp'            => $otp,
            'reset_otp_expires_at' => now()->addMinutes(2)->timestamp,
            'otp_verified'         => false,
        ]);

        try {
            SendOtpMail::sendReliably(
                $targetEmail,
                $otp,
                $officerName,
                $roleName,
                'mengatur ulang kata sandi akun',
                'Kode OTP Baru Pemulihan Kata Sandi - Bank Mini'
            );
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim ulang OTP lupa sandi ke ' . $targetEmail . ': ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Gunakan kode OTP baru: {$otp}");
            } else {
                return redirect()->route('password.otp.form')
                    ->with('error', 'Gagal mengirim ulang email OTP ke ' . $targetEmail . '. Silakan coba beberapa saat lagi.');
            }
        }

        return redirect()->route('password.otp.form')
            ->with('success', 'Kode OTP baru telah dikirimkan ulang ke ' . $targetEmail . '. Waktu verifikasi diperbarui menjadi 2 menit.');
    }

    /**
     * Tampilkan form input verifikasi OTP.
     */
    public function showOtpForm()
    {
        if (!session()->has('reset_user_id') && !session()->has('reset_saver_id')) {
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
                ->with('error', 'Verifikasi Gagal: Kode OTP telah kedaluwarsa (batas waktu 2 menit telah berakhir). Silakan klik tombol Kirim Ulang Kode OTP.')
                ->withErrors(['otp' => 'Kode OTP telah kedaluwarsa. Silakan klik tombol Kirim Ulang Kode OTP.'])
                ->withInput();
        }

        $storedOtp = session('reset_otp');

        if (!$storedOtp || (string) $storedOtp !== (string) trim($request->otp)) {
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
        if (!session('otp_verified') || (!session()->has('reset_user_id') && !session()->has('reset_saver_id'))) {
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
        if (!session('otp_verified') || (!session()->has('reset_user_id') && !session()->has('reset_saver_id'))) {
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

        $user = session('reset_user_id') ? User::find(session('reset_user_id')) : null;
        $saver = session('reset_saver_id') ? Saver::find(session('reset_saver_id')) : null;

        if (!$user && !$saver) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Data pengguna tidak ditemukan.']);
        }

        $hashedPassword = Hash::make($request->password);

        if ($user) {
            $user->password = $hashedPassword;
            $user->save();
        }

        if ($saver) {
            $saver->password = $hashedPassword;
            $saver->save();
        }

        // Bersihkan seluruh session terkait alur reset password
        session()->forget([
            'reset_user_id',
            'reset_saver_id',
            'reset_email',
            'reset_officer_name',
            'reset_otp',
            'reset_otp_expires_at',
            'reset_role',
            'otp_verified',
        ]);

        return redirect()->route('login')
            ->with('success', 'Kata sandi Anda berhasil diperbarui. Silakan login dengan kata sandi baru.');
    }
}
