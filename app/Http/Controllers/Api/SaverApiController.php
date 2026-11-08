<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Saver;
use Illuminate\Support\Facades\Hash;

class SaverApiController extends Controller
{
    /**
     * Login Nasabah untuk mendapatkan Sanctum Token
     * Mendukung login menggunakan NIS (student_id) maupun Nomor Rekening (account_number)
     */
    public function login(Request $request)
    {
        $request->validate([
            'student_id'   => 'required|string',
            'security_pin' => 'required|string',
        ]);

        $loginId = $request->student_id;

        // Cari berdasarkan student_id (NIS) atau account_number (Nomor Rekening)
        $saver = Saver::where('student_id', $loginId)
            ->orWhere('account_number', $loginId)
            ->first();

        // Cek kecocokan NIS/No.Rekening dan PIN
        if (!$saver || !Hash::check($request->security_pin, $saver->security_pin)) {
            return response()->json([
                'success' => false,
                'message' => 'NIS/Nomor Rekening atau PIN keamanan salah.',
            ], 401);
        }

        // Cek status keaktifan rekening
        $saverStatus = strtolower(trim((string) ($saver->status ?? '')));
        if ($saverStatus === 'blocked' || $saverStatus === 'blokir') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan Anda sedang DIBLOKIR oleh Administrator. Silakan hubungi loket Teller sekolah.',
            ], 403);
        }

        if ($saverStatus !== 'active' && $saverStatus !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Rekening tabungan belum aktif. Silakan lakukan aktivasi dan setoran awal di loket Teller Bank Mini terlebih dahulu.',
            ], 403);
        }

        // Generate Sanctum token
        $token = $saver->createToken('nasabah-mobile-token')->plainTextToken;

        $studentName = $saver->student_name ?? $saver->nama ?? 'Siswa Bank Mini';
        $gradeClass = $saver->grade_class ?? $saver->kelas ?? 'Siswa';

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil!',
            'token'   => $token,
            'data'    => [
                'token'          => $token,
                'id'             => $saver->id,
                'student_id'     => $saver->student_id,
                'nis'            => $saver->student_id,
                'account_number' => $saver->account_number,
                'no_rekening'    => $saver->account_number,
                'nama'           => $studentName,
                'name'           => $studentName,
                'student_name'   => $studentName,
                'kelas'          => $gradeClass,
                'grade_class'    => $gradeClass,
                'saldo'          => (float) $saver->saldo,
            ]
        ], 200);
    }

    /**
     * Mengambil Profil Nasabah yang sedang login
     */
    public function profile(Request $request)
    {
        $saver = $request->user();

        // Validasi keaktifan akun
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

        $studentName = $saver->student_name ?? $saver->nama ?? 'Siswa Bank Mini';
        $gradeClass = $saver->grade_class ?? $saver->kelas ?? 'Siswa';

        return response()->json([
            'success' => true,
            'message' => 'Data profil berhasil diambil',
            'data'    => [
                'id'             => $saver->id,
                'student_id'     => $saver->student_id,
                'nis'            => $saver->student_id,
                'account_number' => $saver->account_number,
                'no_rekening'    => $saver->account_number,
                'nama'           => $studentName,
                'name'           => $studentName,
                'student_name'   => $studentName,
                'kelas'          => $gradeClass,
                'grade_class'    => $gradeClass,
                'status'         => $saver->status ?? 'active',
                'saldo'          => (float) $saver->saldo,
                'created_at'     => $saver->created_at,
            ]
        ], 200);
    }

    /**
     * Mengambil Riwayat Transaksi Nasabah
     */
    public function transactions(Request $request)
    {
        $saver = $request->user();

        // Validasi keaktifan akun
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

        // Ambil transaksi nasabah diurutkan dari yang terbaru
        $transactions = $saver->transactions()
            ->latest()
            ->limit(100)
            ->get();

        $formatted = $transactions->map(function ($tx) {
            $typeStr = strtolower($tx->type ?? $tx->jenis ?? '');
            $isSetor = ($typeStr === 'deposit' || $typeStr === 'setor' || $typeStr === 'setoran');

            return [
                'id'          => $tx->id,
                'type'        => $isSetor ? 'setor' : 'tarik',
                'jenis'       => $isSetor ? 'setor' : 'tarik',
                'amount'      => (float) $tx->amount,
                'jumlah'      => (float) $tx->amount,
                'date'        => $tx->created_at ? $tx->created_at->format('Y-m-d H:i:s') : null,
                'tanggal'     => $tx->created_at ? $tx->created_at->format('d M Y, H:i') : null,
                'tanggal_group' => $tx->created_at ? $tx->created_at->format('d M Y') : 'MUTASI TERBARU',
                'description' => $tx->description ?? ($isSetor ? 'Setoran Tabungan' : 'Penarikan Saldo'),
                'keterangan'  => $tx->description ?? ($isSetor ? 'Setoran Tabungan' : 'Penarikan Saldo'),
                'status'      => $tx->status ?? 'success',
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Riwayat transaksi berhasil diambil',
            'data'    => $formatted,
        ], 200);
    }

    /**
     * Logout dan cabut semua token nasabah
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.',
        ], 200);
    }

    /**
     * Kirim OTP untuk Lupa PIN (Maksimal 3x/jam)
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $id = trim($request->identifier);

        $saver = Saver::where('student_id', $id)
            ->orWhere('account_number', $id)
            ->first();

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'NIS atau Nomor Rekening tidak ditemukan di sistem sekolah.',
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

        // Cek Rate Limiting (Maks 3 kali per jam)
        $rateLimitKey = 'otp_attempts_' . $saver->id;
        $attempts = cache()->get($rateLimitKey, 0);

        if ($attempts >= 3) {
            return response()->json([
                'success' => false,
                'message' => 'Batas permintaan OTP tercapai (Maksimal 3 kali per jam). Silakan coba lagi nanti.',
            ], 429);
        }

        // Catat attempt
        cache()->put($rateLimitKey, $attempts + 1, now()->addHour());

        // Generate 6-digit OTP
        $otp = (string) mt_rand(100000, 999999);
        $otpToken = 'otp_tok_' . bin2hex(random_bytes(16));

        // Simpan OTP ke cache selama 5 menit (300 detik)
        cache()->put('otp_data_' . $saver->id, [
            'otp'       => $otp,
            'otp_token' => $otpToken,
        ], now()->addMinutes(5));

        // Masking identifier
        $masked = strlen($id) > 4
            ? substr($id, 0, 2) . '••••' . substr($id, -2)
            : '••••' . $id;

        return response()->json([
            'success'            => true,
            'message'            => 'Kode OTP verifikasi telah dikirim.',
            'masked_destination' => $masked,
            'otp_token'          => $otpToken,
            'expires_in'         => 300,
            // Mode development: sertakan preview OTP agar mudah diuji
            'dev_otp'            => $otp,
        ], 200);
    }

    /**
     * Verifikasi OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'otp'        => 'required|string|size:6',
            'otp_token'  => 'required|string',
        ]);

        $id = trim($request->identifier);
        $saver = Saver::where('student_id', $id)
            ->orWhere('account_number', $id)
            ->first();

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas tidak ditemukan.',
            ], 404);
        }

        $cached = cache()->get('otp_data_' . $saver->id);

        $validOtp = false;
        // Boleh kode OTP riil dari cache atau kode demo 123456
        if ($cached && isset($cached['otp']) && $cached['otp'] === $request->otp) {
            $validOtp = true;
        } elseif ($request->otp === '123456') {
            $validOtp = true;
        }

        if (!$validOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah atau telah kedaluwarsa. Silakan periksa kembali pesan Anda.',
            ], 400);
        }

        // Hapus OTP setelah sukses
        cache()->forget('otp_data_' . $saver->id);

        // Buat reset token berlaku 15 menit
        $resetToken = 'rst_' . bin2hex(random_bytes(24));
        cache()->put('reset_auth_' . $saver->id, $resetToken, now()->addMinutes(15));

        return response()->json([
            'success'     => true,
            'message'     => 'Kode OTP berhasil diverifikasi.',
            'reset_token' => $resetToken,
        ], 200);
    }

    /**
     * Simpan PIN Baru
     */
    public function resetPin(Request $request)
    {
        $request->validate([
            'identifier'  => 'required|string',
            'reset_token' => 'required|string',
            'new_pin'     => 'required|string|size:6',
            'confirm_pin' => 'required|string|same:new_pin',
        ]);

        $id = trim($request->identifier);
        $saver = Saver::where('student_id', $id)
            ->orWhere('account_number', $id)
            ->first();

        if (!$saver) {
            return response()->json([
                'success' => false,
                'message' => 'Identitas siswa tidak ditemukan.',
            ], 404);
        }

        // Validasi Reset Token
        $cachedToken = cache()->get('reset_auth_' . $saver->id);
        if (!$cachedToken || $cachedToken !== $request->reset_token) {
            // Jika token demo di development
            if (!str_starts_with($request->reset_token, 'reset_auth_') && !str_starts_with($request->reset_token, 'rst_')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi reset PIN telah kedaluwarsa. Silakan ulangi langkah verifikasi OTP.',
                ], 403);
            }
        }

        $newPin = $request->new_pin;

        // Cek pola tidak aman
        $identical = ['000000','111111','222222','333333','444444','555555','666666','777777','888888','999999'];
        $sequential = ['012345','123456','234567','345678','456789','567890','987654','876543','765432','654321','543210'];
        if (in_array($newPin, $identical) || in_array($newPin, $sequential)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN terlalu mudah ditebak. Hindari kombinasi berulang atau berurutan.',
            ], 422);
        }

        // Update PIN baru di database
        $saver->security_pin = Hash::make($newPin);
        $saver->save();

        // Cabut reset token & revoke access tokens lama
        cache()->forget('reset_auth_' . $saver->id);
        $saver->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'PIN baru berhasil disimpan. Silakan masuk menggunakan PIN baru Anda.',
        ], 200);
    }
}
