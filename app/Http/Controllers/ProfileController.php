<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SendOtpMail;
use App\Models\User;
use App\Models\Officer;

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil pengguna
     */
    public function show()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    /**
     * Minta kode OTP untuk Ganti Kata Sandi (dikirim ke email yang diminta atau email akun saat ini)
     */
    public function requestPasswordOtp(Request $request)
    {
        $user = auth()->user();

        $targetEmail = strtolower(trim((string) ($request->input('email') ?: session('profile_pw_otp_email') ?: $user->email)));

        if (empty($targetEmail) || !filter_var($targetEmail, FILTER_VALIDATE_EMAIL)) {
            return back()->with('active_tab', 'password')->with('error', 'Silakan masukkan alamat email aktif yang valid untuk menerima kode OTP.');
        }

        // Generate 6 digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Simpan ke session dengan batasan waktu 2 menit (120 detik)
        session([
            'profile_pw_otp'            => $otp,
            'profile_pw_otp_email'      => $targetEmail,
            'profile_pw_otp_expires_at' => now()->addMinutes(2)->timestamp,
            'profile_pw_otp_sent'       => true,
            'profile_pw_otp_verified'   => false,
        ]);

        $officerName = $user->officer_name ?? $user->username;
        $roleName    = $user->role ?? 'Pegawai';

        $emailSent = false;
        try {
            SendOtpMail::sendReliably(
                $targetEmail, 
                $otp, 
                $officerName, 
                $roleName, 
                'mengubah kata sandi profil',
                'Kode OTP Ganti Kata Sandi - Bank Mini'
            );
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim email OTP ganti password ke ' . $targetEmail . ': ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Email belum terkirim ({$e->getMessage()}). Gunakan kode OTP: {$otp}");
            } else {
                return back()->with('active_tab', 'password')->with('error', 'Gagal mengirim email OTP ke ' . $targetEmail . '. Pastikan alamat email benar dan aktif.');
            }
        }

        return back()->with('active_tab', 'password')->with('success', $emailSent 
            ? 'Berhasil! Kode OTP 6-digit telah dikirimkan ke email (' . $targetEmail . '). Berlaku selama 2 menit.' 
            : 'Kode OTP keamanan telah dibuat untuk sesi ini. Silakan masukkan kode OTP.');
    }

    /**
     * Kirim ulang kode OTP Ganti Kata Sandi (dapat diklik sebelum maupun sesudah waktu 2 menit habis)
     */
    public function resendPasswordOtp(Request $request)
    {
        return $this->requestPasswordOtp($request);
    }

    /**
     * Langkah 2: Verifikasi kode OTP untuk Ganti Kata Sandi sebelum masuk ke form Password Baru
     */
    public function verifyPasswordOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Kode OTP wajib diisi.',
            'otp.digits'   => 'Kode OTP harus berjumlah 6 digit angka.',
        ]);

        // Cek apakah pernah meminta OTP
        if (!session('profile_pw_otp_sent')) {
            return back()->with('active_tab', 'password')->with('error', 'Sesi OTP tidak ditemukan. Silakan minta kode OTP terlebih dahulu.');
        }

        // Cek kedaluwarsa waktu 2 menit
        $expiresAt = session('profile_pw_otp_expires_at');
        if (!$expiresAt || now()->timestamp > $expiresAt) {
            return back()->withInput()->with('active_tab', 'password')->with('error', 'Verifikasi Gagal: Kode OTP telah kedaluwarsa (melebihi batas waktu 2 menit). Silakan minta kode OTP baru.');
        }

        // Cek kesesuaian OTP
        $storedOtp = session('profile_pw_otp');
        if (!$storedOtp || (string) $storedOtp !== (string) trim($request->otp)) {
            return back()->withInput()->with('active_tab', 'password')->with('error', 'Verifikasi Gagal: Kode OTP yang Anda masukkan salah atau tidak sesuai! Silakan periksa kembali.');
        }

        // Tandai OTP sudah terverifikasi agar beralih ke Langkah 3 (Input Sandi Baru)
        session(['profile_pw_otp_verified' => true]);

        return back()->with('active_tab', 'password')->with('success', 'Verifikasi OTP Berhasil! Sekarang silakan buat kata sandi baru Anda.');
    }

    /**
     * Batalkan / Reset alur OTP Ganti Kata Sandi
     */
    public function cancelPasswordOtp()
    {
        session()->forget([
            'profile_pw_otp',
            'profile_pw_otp_email',
            'profile_pw_otp_expires_at',
            'profile_pw_otp_sent',
            'profile_pw_otp_verified',
        ]);

        return back()->with('active_tab', 'password')->with('success', 'Alur perubahan kata sandi telah direset.');
    }

    /**
     * Langkah 3: Eksekusi pembaruan kata sandi setelah OTP terverifikasi
     */
    public function updatePassword(Request $request)
    {
        // Jika belum terverifikasi di langkah 2 tetapi mengirim otp langsung (fallback)
        if (!session('profile_pw_otp_verified')) {
            if ($request->filled('otp')) {
                $expiresAt = session('profile_pw_otp_expires_at');
                if (!$expiresAt || now()->timestamp > $expiresAt) {
                    return back()->withInput()->with('active_tab', 'password')->with('error', 'Gagal: Kode OTP telah kedaluwarsa (melebihi batas waktu 2 menit). Silakan minta kode OTP baru.');
                }
                $storedOtp = session('profile_pw_otp');
                if (!$storedOtp || (string) $storedOtp !== (string) trim($request->otp)) {
                    return back()->withInput()->with('active_tab', 'password')->with('error', 'Gagal: Kode OTP yang Anda masukkan salah atau tidak cocok.');
                }
            } else {
                return back()->with('active_tab', 'password')->with('error', 'Anda wajib memverifikasi kode OTP terlebih dahulu sebelum membuat kata sandi baru.');
            }
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'password')->with('error', 'Gagal menyimpan kata sandi: ' . $validator->errors()->first());
        }

        // Update password user
        /** @var User $user */
        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        // Bersihkan session
        session()->forget([
            'profile_pw_otp',
            'profile_pw_otp_email',
            'profile_pw_otp_expires_at',
            'profile_pw_otp_sent',
            'profile_pw_otp_verified',
        ]);

        return back()->with('active_tab', 'password')->with('success', 'Berhasil! Kata sandi akun Anda telah diperbarui dengan aman.');
    }

    /**
     * Minta kode OTP untuk Ganti Email (Verifikasi password saat ini & kirim OTP ke email baru)
     */
    public function requestEmailOtp(Request $request)
    {
        $user = auth()->user();

        // Jika dipanggil sebagai kirim ulang OTP tanpa input form baru
        if (!$request->filled('new_email') && session('change_email_new')) {
            return $this->resendEmailOtp($request);
        }

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'new_email'        => 'required|email|max:255',
            'current_password' => 'required|string',
        ], [
            'new_email.required'        => 'Alamat email baru wajib diisi.',
            'new_email.email'           => 'Format alamat email baru tidak valid.',
            'current_password.required' => 'Kata sandi saat ini wajib diisi untuk verifikasi keamanan.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'email')->with('error', 'Gagal memproses: ' . $validator->errors()->first());
        }

        // 1. Verifikasi kecocokan password saat ini
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withInput()->with('active_tab', 'email')->with('error', 'Verifikasi Gagal: Kata sandi saat ini yang Anda masukkan salah.');
        }

        $newEmail = strtolower(trim($request->new_email));

        // 2. Generate 6 digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // 3. Simpan data ke session dengan batasan waktu 2 menit (120 detik)
        session([
            'change_email_new'        => $newEmail,
            'change_email_otp'        => $otp,
            'change_email_expires_at' => now()->addMinutes(2)->timestamp,
            'change_email_otp_sent'   => true,
        ]);

        $officerName = $user->officer_name ?? $user->username;
        $roleName    = $user->role ?? 'Pegawai';

        $emailSent = false;
        try {
            SendOtpMail::sendReliably(
                $newEmail, 
                $otp, 
                $officerName, 
                $roleName, 
                'memverifikasi perubahan alamat email baru',
                'Kode OTP Verifikasi Email Baru - Bank Mini'
            );
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim email OTP ganti email ke ' . $newEmail . ': ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Email belum terkirim ({$e->getMessage()}). Gunakan kode OTP verifikasi email: {$otp}");
            } else {
                return back()->withInput()->with('active_tab', 'email')->with('error', 'Gagal mengirim email OTP ke ' . $newEmail . '. Pastikan alamat email benar dan aktif.');
            }
        }

        return back()->with('active_tab', 'email')->with('success', $emailSent 
            ? 'Berhasil! Kode OTP verifikasi telah dikirimkan ke email baru Anda (' . $newEmail . '). Berlaku selama 2 menit.' 
            : 'Kode OTP verifikasi email baru telah dibuat untuk sesi ini.');
    }

    /**
     * Kirim ulang kode OTP untuk Ganti Email Baru (dapat diklik sebelum maupun sesudah waktu 2 menit habis)
     */
    public function resendEmailOtp(Request $request)
    {
        $user = auth()->user();
        $newEmail = session('change_email_new');

        if (!$newEmail) {
            return back()->with('active_tab', 'email')->with('error', 'Sesi perubahan email tidak ditemukan. Silakan masukkan kembali alamat email baru Anda.');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'change_email_otp'        => $otp,
            'change_email_expires_at' => now()->addMinutes(2)->timestamp,
            'change_email_otp_sent'   => true,
        ]);

        $officerName = $user->officer_name ?? $user->username;
        $roleName    = $user->role ?? 'Pegawai';

        try {
            SendOtpMail::sendReliably(
                $newEmail,
                $otp,
                $officerName,
                $roleName,
                'memverifikasi perubahan alamat email baru',
                'Kode OTP Baru Verifikasi Email Baru - Bank Mini'
            );
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim ulang OTP ganti email ke ' . $newEmail . ': ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Gunakan kode OTP baru: {$otp}");
            } else {
                return back()->with('active_tab', 'email')->with('error', 'Gagal mengirim ulang email OTP ke ' . $newEmail . '.');
            }
        }

        return back()->with('active_tab', 'email')->with('success', 'Kode OTP baru telah dikirimkan ulang ke ' . $newEmail . '. Waktu verifikasi diperbarui menjadi 2 menit.');
    }

    /**
     * Verifikasi kode OTP dan resmi perbarui email akun
     */
    public function verifyAndChangeEmail(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Kode OTP verifikasi email wajib diisi.',
            'otp.digits'   => 'Kode OTP harus berjumlah 6 digit angka.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('active_tab', 'email')->with('error', $validator->errors()->first());
        }

        $newEmail = session('change_email_new');
        if (!$newEmail) {
            return back()->with('active_tab', 'email')->with('error', 'Sesi perubahan email telah berakhir. Silakan masukkan email baru kembali.');
        }

        // Cek batas waktu 2 menit
        $expiresAt = session('change_email_expires_at');
        if (!$expiresAt || now()->timestamp > $expiresAt) {
            return back()->with('active_tab', 'email')->with('error', 'Verifikasi Gagal: Kode OTP telah kedaluwarsa (melebihi batas waktu 2 menit). Silakan klik tombol Kirim Ulang Kode OTP.');
        }

        // Cek kesesuaian OTP
        $storedOtp = session('change_email_otp');
        if (!$storedOtp || (string) $storedOtp !== (string) trim($request->otp)) {
            return back()->with('active_tab', 'email')->with('error', 'Verifikasi Gagal: Kode OTP yang Anda masukkan salah atau tidak cocok!');
        }

        /** @var User $user */
        $user = auth()->user();

        // Bebaskan email dari akun lain jika sempat dipakai saat pengujian agar tidak terjadi konflik unique constraint
        User::where('email', $newEmail)->where('id', '!=', $user->id)->update(['email' => null]);

        // Update email akun
        $user->email = $newEmail;
        $user->save();

        // Sinkronkan ke data Officer jika ada relasi
        try {
            if ($user->officer) {
                $user->officer->update(['email' => $newEmail]);
            }
        } catch (\Throwable $e) {
            // Abaikan jika relasi tidak ada
        }

        // Bersihkan session
        session()->forget([
            'change_email_new',
            'change_email_otp',
            'change_email_expires_at',
            'change_email_otp_sent',
        ]);

        return back()->with('active_tab', 'email')->with('success', 'Berhasil! Alamat email akun Anda telah diperbarui menjadi: ' . $newEmail);
    }

    /**
     * Batalkan proses ganti email
     */
    public function cancelEmailOtp()
    {
        session()->forget([
            'change_email_new',
            'change_email_otp',
            'change_email_expires_at',
            'change_email_otp_sent',
        ]);

        return back()->with('active_tab', 'email')->with('success', 'Alur perubahan email telah dibatalkan.');
    }
}

