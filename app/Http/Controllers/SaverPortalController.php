<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\SendOtpMail;

class SaverPortalController extends Controller
{
    /**
     * Tampilkan form login (Dialihkan ke login terintegrasi utama dengan pesan)
     */
    public function showLoginForm()
    {
        return redirect()->route('login')->with('error', 'Akses login web hanya diperuntukkan bagi Admin & Teller. Nasabah dianjurkan untuk menggunakan Aplikasi Mobile (APK) Bank Mini.');
    }

    /**
     * Proses login (Fallback dialihkan ke AuthController)
     */
    public function login(Request $request)
    {
        return app(AuthController::class)->login($request);
    }

    /**
     * Helper untuk mengambil data nasabah aktif yang sedang login
     */
    protected function getActiveSaver(): ?Saver
    {
        $saverId = Session::get('saver_id');

        if (!$saverId && Auth::check() && Auth::user()->role === 'Saver') {
            $user = Auth::user();
            $saver = Saver::where('username', $user->username)
                ->orWhere('email', $user->email)
                ->orWhere('student_id', str_replace('siswa_', '', $user->username))
                ->first();

            if (!$saver) {
                $saver = Saver::where('student_name', $user->officer_name)->first();
            }

            if ($saver) {
                Session::put('saver_id', $saver->id);
                Session::put('account_number', $saver->account_number);
                $saverId = $saver->id;
            }
        }

        if (!$saverId) {
            return null;
        }

        return Saver::find($saverId);
    }

    /**
     * Halaman Dashboard Nasabah (Cek Saldo Mandiri, Kartu SimPel, QR Loket & Buku Mutasi)
     */
    public function dashboard(Request $request)
    {
        $saver = $this->getActiveSaver();

        if (!$saver) {
            return redirect()->route('login')->with('error', 'Sesi Anda telah berakhir. Silakan login kembali.');
        }

        // Ambil riwayat mutasi/transaksi milik nasabah tersebut secara read-only
        $transactions = Transaction::with('user')
            ->where('saver_id', $saver->id)
            ->where('status', '!=', 'Failed')
            ->orderBy('created_at', 'desc')
            ->get();

        // Hitung total setoran & penarikan
        $totalDeposits = $transactions->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])->sum('amount');
        $totalWithdrawals = $transactions->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])->sum('amount');

        // Hitung running balance secara kronologis
        $chronological = $transactions->sortBy('created_at')->values();
        $runningBalance = 0;
        $balanceMap = [];

        foreach ($chronological as $trx) {
            $isDeposit = in_array($trx->transaction_type, ['Deposit', 'deposit', 'setor', 'setoran']);
            if ($isDeposit) {
                $runningBalance += $trx->amount;
            } else {
                $runningBalance -= $trx->amount;
            }
            $balanceMap[$trx->id] = $runningBalance;
        }

        // Lampirkan saldo mutasi ke objek transaksi
        foreach ($transactions as $trx) {
            $trx->running_balance = $balanceMap[$trx->id] ?? $saver->saldo;
        }

        // Ambil Target Menabung (Savings Goal) dari Session atau default
        $savingsGoal = session('savings_goal_' . $saver->id, [
            'title'  => 'Tabungan Masa Depan / Perlengkapan Jurusan',
            'target' => 500000,
        ]);

        return view('saver.dashboard', compact(
            'saver',
            'transactions',
            'totalDeposits',
            'totalWithdrawals',
            'savingsGoal'
        ));
    }

    /**
     * Halaman Cetak / Rekening Koran Resmi (e-Statement)
     */
    public function statement(Request $request)
    {
        $saver = $this->getActiveSaver();

        if (!$saver) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $query = Transaction::with('user')
            ->where('saver_id', $saver->id)
            ->where('status', '!=', 'Failed');

        // Filter periode jika disediakan
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        $transactions = $query->orderBy('created_at', 'asc')->get();

        $totalDeposits = $transactions->whereIn('transaction_type', ['Deposit', 'deposit', 'setor', 'setoran'])->sum('amount');
        $totalWithdrawals = $transactions->whereIn('transaction_type', ['Withdrawal', 'withdrawal', 'tarik', 'penarikan'])->sum('amount');

        // Hitung running balance
        $currentBalance = 0;
        foreach ($transactions as $trx) {
            $isDeposit = in_array($trx->transaction_type, ['Deposit', 'deposit', 'setor', 'setoran']);
            if ($isDeposit) {
                $currentBalance += $trx->amount;
            } else {
                $currentBalance -= $trx->amount;
            }
            $trx->running_balance = $currentBalance;
        }

        return view('saver.statement', compact(
            'saver',
            'transactions',
            'totalDeposits',
            'totalWithdrawals'
        ));
    }

    /**
     * Memperbarui Target Menabung Siswa (Savings Goal)
     */
    public function updateSavingsGoal(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        $request->validate([
            'goal_title'  => 'required|string|max:100',
            'goal_amount' => 'required|numeric|min:10000',
        ], [
            'goal_title.required'  => 'Nama target menabung wajib diisi.',
            'goal_amount.required' => 'Nominal target menabung wajib diisi.',
            'goal_amount.min'      => 'Target menabung minimal Rp 10.000.',
        ]);

        session([
            'savings_goal_' . $saver->id => [
                'title'  => $request->goal_title,
                'target' => (float)$request->goal_amount,
            ]
        ]);

        return back()->with('success', 'Target menabung berhasil diperbarui!');
    }

    /**
     * Memperbarui PIN Keamanan atau Password Mandiri
     */
    public function updateSecurity(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        $actionType = $request->input('action_type', 'pin');

        if ($actionType === 'pin') {
            $request->validate([
                'current_pin'      => 'required|digits:6',
                'new_pin'          => 'required|digits:6|confirmed',
            ], [
                'current_pin.required'  => 'PIN lama wajib diisi.',
                'current_pin.digits'    => 'PIN lama harus 6 digit angka.',
                'new_pin.required'      => 'PIN baru wajib diisi.',
                'new_pin.digits'        => 'PIN baru harus 6 digit angka.',
                'new_pin.confirmed'     => 'Konfirmasi PIN baru tidak cocok.',
            ]);

            if (!empty($saver->security_pin) && !Hash::check($request->current_pin, $saver->security_pin)) {
                return back()->with('error', 'PIN keamanan lama yang Anda masukkan salah.');
            }

            $saver->update([
                'security_pin' => Hash::make($request->new_pin),
            ]);

            return back()->with('success', 'PIN keamanan 6-digit berhasil diperbarui!');
        } else {
            // Update Password
            $request->validate([
                'current_password'  => 'required|string',
                'new_password'      => 'required|string|min:6|confirmed',
            ], [
                'current_password.required' => 'Kata sandi saat ini wajib diisi.',
                'new_password.required'     => 'Kata sandi baru wajib diisi.',
                'new_password.min'          => 'Kata sandi baru minimal 6 karakter.',
                'new_password.confirmed'    => 'Konfirmasi kata sandi baru tidak cocok.',
            ]);

            $isPasswordValid = false;
            if (!empty($saver->password) && Hash::check($request->current_password, $saver->password)) {
                $isPasswordValid = true;
            } elseif (!empty($saver->security_pin) && Hash::check($request->current_password, $saver->security_pin)) {
                $isPasswordValid = true;
            }

            if (!$isPasswordValid) {
                return back()->with('error', 'Kata sandi saat ini yang Anda masukkan salah.');
            }

            $newHash = Hash::make($request->new_password);
            $saver->update(['password' => $newHash]);

            // Update user terkait jika ada
            $user = User::where('username', $saver->username)
                ->orWhere('username', 'siswa_' . $saver->student_id)
                ->orWhere('email', $saver->email)
                ->first();

            if ($user) {
                $user->update(['password' => $newHash]);
            }

            return back()->with('success', 'Kata sandi portal berhasil diperbarui!');
        }
    }

    /**
     * Halaman Profil Siswa Nasabah
     */
    public function profile(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        return view('saver.profile', compact('saver'));
    }

    /**
     * Minta Kode OTP untuk Ganti Kata Sandi Nasabah (Batas waktu 2 menit)
     */
    public function requestPasswordOtp(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        if (empty($saver->email)) {
            return back()->with('error', 'Akun tabungan Anda belum memiliki alamat email terdaftar. Silakan hubungi loket Teller sekolah atau atur email terlebih dahulu.');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'saver_pw_otp'            => $otp,
            'saver_pw_otp_expires_at' => now()->addMinutes(2)->timestamp,
            'saver_pw_otp_sent'       => true,
        ]);

        $emailSent = false;
        try {
            Mail::to($saver->email)->send(new SendOtpMail(
                $otp,
                $saver->student_name,
                'Nasabah SimPel',
                'mengubah kata sandi akun portal tabungan',
                'Kode OTP Ganti Kata Sandi Portal Nasabah - Bank Mini'
            ));
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim email OTP ganti password nasabah: ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Email belum terkirim ({$e->getMessage()}). Gunakan kode OTP: {$otp}");
            } else {
                return back()->with('error', 'Gagal mengirim email OTP: ' . $e->getMessage());
            }
        }

        return back()->with('success', $emailSent
            ? 'Kode OTP telah dikirimkan ke email Anda (' . $saver->email . '). Berlaku selama 2 menit.'
            : 'Kode OTP keamanan telah dibuat untuk sesi ini.');
    }

    /**
     * Eksekusi Perbarui Kata Sandi Nasabah via OTP (Batas waktu 2 menit)
     */
    public function updatePasswordWithOtp(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        $request->validate([
            'otp'      => 'required|digits:6',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'otp.required'       => 'Kode OTP wajib diisi.',
            'otp.digits'         => 'Kode OTP harus 6 digit angka.',
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        // Cek kadaluarsa waktu 2 menit
        $expiresAt = session('saver_pw_otp_expires_at');
        if (!$expiresAt || now()->timestamp > $expiresAt) {
            return back()->withInput()->with('error', 'Kode OTP telah kedaluwarsa (melebihi batas waktu 2 menit). Silakan minta kode OTP baru.');
        }

        // Cek kesesuaian OTP
        $storedOtp = session('saver_pw_otp');
        if (!$storedOtp || $storedOtp !== $request->otp) {
            return back()->withInput()->with('error', 'Kode OTP yang Anda masukkan salah atau tidak cocok.');
        }

        $newHash = Hash::make($request->password);
        $saver->update(['password' => $newHash]);

        // Sinkronkan juga ke tabel users jika ada
        $user = User::where('username', $saver->username)
            ->orWhere('username', 'siswa_' . $saver->student_id)
            ->orWhere('email', $saver->email)
            ->first();

        if ($user) {
            $user->update(['password' => $newHash]);
        }

        // Hapus session OTP
        session()->forget([
            'saver_pw_otp',
            'saver_pw_otp_expires_at',
            'saver_pw_otp_sent',
        ]);

        return back()->with('success', 'Kata sandi portal tabungan berhasil diperbarui dengan aman!');
    }

    /**
     * Minta Kode OTP untuk Ganti PIN 6-Digit Nasabah (Batas waktu 2 menit)
     */
    public function requestPinOtp(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        if (empty($saver->email)) {
            return back()->with('error', 'Akun tabungan Anda belum memiliki alamat email terdaftar. Silakan hubungi loket Teller sekolah atau atur email terlebih dahulu.');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'saver_pin_otp'            => $otp,
            'saver_pin_otp_expires_at' => now()->addMinutes(2)->timestamp,
            'saver_pin_otp_sent'       => true,
        ]);

        $emailSent = false;
        try {
            Mail::to($saver->email)->send(new SendOtpMail(
                $otp,
                $saver->student_name,
                'Nasabah SimPel',
                'mengubah PIN 6-digit transaksi loket',
                'Kode OTP Ganti PIN 6-Digit - Bank Mini'
            ));
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim email OTP ganti PIN nasabah: ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Email belum terkirim ({$e->getMessage()}). Gunakan kode OTP PIN: {$otp}");
            } else {
                return back()->with('error', 'Gagal mengirim email OTP: ' . $e->getMessage());
            }
        }

        return back()->with('success', $emailSent
            ? 'Kode OTP ganti PIN telah dikirimkan ke email Anda (' . $saver->email . '). Berlaku selama 2 menit.'
            : 'Kode OTP ganti PIN telah dibuat untuk sesi ini.');
    }

    /**
     * Eksekusi Perbarui PIN 6-Digit Nasabah via OTP (Batas waktu 2 menit)
     */
    public function updatePinWithOtp(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        $request->validate([
            'otp'      => 'required|digits:6',
            'pin'      => 'required|digits:6|confirmed',
        ], [
            'otp.required'   => 'Kode OTP wajib diisi.',
            'otp.digits'     => 'Kode OTP harus 6 digit angka.',
            'pin.required'   => 'PIN baru wajib diisi.',
            'pin.digits'     => 'PIN baru harus tepat 6 digit angka numerik.',
            'pin.confirmed'  => 'Konfirmasi PIN 6-digit baru tidak cocok.',
        ]);

        // Cek kedaluwarsa waktu 2 menit
        $expiresAt = session('saver_pin_otp_expires_at');
        if (!$expiresAt || now()->timestamp > $expiresAt) {
            return back()->withInput()->with('error', 'Kode OTP telah kedaluwarsa (melebihi batas waktu 2 menit). Silakan minta kode OTP baru.');
        }

        // Cek kesesuaian OTP
        $storedOtp = session('saver_pin_otp');
        if (!$storedOtp || $storedOtp !== $request->otp) {
            return back()->withInput()->with('error', 'Kode OTP yang Anda masukkan salah atau tidak cocok.');
        }

        $saver->update([
            'security_pin' => Hash::make($request->pin),
        ]);

        // Hapus session OTP
        session()->forget([
            'saver_pin_otp',
            'saver_pin_otp_expires_at',
            'saver_pin_otp_sent',
        ]);

        return back()->with('success', 'PIN keamanan 6-digit transaksi loket berhasil diperbarui!');
    }

    /**
     * Minta Kode OTP untuk Ganti Email Nasabah (Verifikasi Password/PIN + Kirim OTP ke Email Baru, Batas 2 menit)
     */
    public function requestEmailOtp(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        $request->validate([
            'new_email'        => 'required|email|unique:savers,email,' . $saver->id,
            'auth_credential'  => 'required|string',
        ], [
            'new_email.required'        => 'Alamat email baru wajib diisi.',
            'new_email.email'           => 'Format email baru tidak valid.',
            'new_email.unique'          => 'Alamat email tersebut sudah digunakan oleh akun nasabah lain.',
            'auth_credential.required'  => 'Kata sandi atau PIN keamanan saat ini wajib diisi.',
        ]);

        // Verifikasi kata sandi atau PIN
        $isAuthValid = false;
        if (!empty($saver->password) && Hash::check($request->auth_credential, $saver->password)) {
            $isAuthValid = true;
        } elseif (!empty($saver->security_pin) && Hash::check($request->auth_credential, $saver->security_pin)) {
            $isAuthValid = true;
        }

        if (!$isAuthValid) {
            return back()->withInput()->with('error', 'Kata sandi atau PIN keamanan saat ini yang Anda masukkan salah.');
        }

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        session([
            'saver_change_email_new'        => $request->new_email,
            'saver_change_email_otp'        => $otp,
            'saver_change_email_expires_at' => now()->addMinutes(2)->timestamp,
            'saver_change_email_otp_sent'   => true,
        ]);

        $emailSent = false;
        try {
            Mail::to($request->new_email)->send(new SendOtpMail(
                $otp,
                $saver->student_name,
                'Nasabah SimPel',
                'memverifikasi perubahan alamat email baru tabungan SimPel',
                'Kode OTP Verifikasi Email Baru Nasabah - Bank Mini'
            ));
            $emailSent = true;
        } catch (\Throwable $e) {
            Log::warning('Gagal kirim email OTP ganti email nasabah: ' . $e->getMessage());
            if (config('app.debug')) {
                session()->flash('dev_otp_notice', "Mode Pengujian Lokal: Email belum terkirim ({$e->getMessage()}). Gunakan kode OTP email baru: {$otp}");
            } else {
                return back()->withInput()->with('error', 'Gagal mengirim email OTP ke email baru: ' . $e->getMessage());
            }
        }

        return back()->with('success', $emailSent
            ? 'Kode OTP telah dikirimkan ke email baru Anda (' . $request->new_email . '). Berlaku selama 2 menit.'
            : 'Kode OTP verifikasi email baru telah dibuat untuk sesi ini.');
    }

    /**
     * Eksekusi Verifikasi Kode OTP dan Perbarui Email Nasabah
     */
    public function verifyAndChangeEmail(Request $request)
    {
        $saver = $this->getActiveSaver();
        if (!$saver) {
            return redirect()->route('login');
        }

        $request->validate([
            'otp' => 'required|digits:6',
        ], [
            'otp.required' => 'Kode OTP verifikasi email wajib diisi.',
            'otp.digits'   => 'Kode OTP harus berjumlah 6 digit angka.',
        ]);

        $newEmail = session('saver_change_email_new');
        if (!$newEmail) {
            return back()->with('error', 'Sesi perubahan email telah berakhir. Silakan masukkan email baru kembali.');
        }

        // Cek kedaluwarsa waktu 2 menit
        $expiresAt = session('saver_change_email_expires_at');
        if (!$expiresAt || now()->timestamp > $expiresAt) {
            return back()->with('error', 'Kode OTP telah kedaluwarsa (melebihi batas waktu 2 menit). Silakan minta kode OTP baru.');
        }

        // Cek kesesuaian OTP
        $storedOtp = session('saver_change_email_otp');
        if (!$storedOtp || $storedOtp !== $request->otp) {
            return back()->with('error', 'Kode OTP yang Anda masukkan salah atau tidak cocok.');
        }

        // Perbarui email pada tabel savers
        $saver->email = $newEmail;
        $saver->save();

        // Perbarui juga pada akun users jika ada
        $user = User::where('username', $saver->username)
            ->orWhere('username', 'siswa_' . $saver->student_id)
            ->first();

        if ($user) {
            $user->email = $newEmail;
            $user->save();
        }

        // Bersihkan session
        session()->forget([
            'saver_change_email_new',
            'saver_change_email_otp',
            'saver_change_email_expires_at',
            'saver_change_email_otp_sent',
        ]);

        return back()->with('success', 'Alamat email nasabah berhasil diperbarui menjadi ' . $newEmail . '!');
    }

    /**
     * Logout nasabah
     */
    public function logout(Request $request)
    {
        if (Auth::check()) {
            Auth::logout();
        }

        Session::forget(['saver_id', 'account_number']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari akun nasabah.');
    }
}
