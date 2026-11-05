<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use App\Models\Saver;

class AuthController extends Controller
{
    // Menampilkan halaman form login terintegrasi
    public function showLoginForm()
    {
        // Jika sudah login, langsung arahkan ke dashboard masing-masing
        if (Auth::check()) {
            $userRole = Auth::user()->role;
            if (in_array($userRole, ['Administrator', 'Supervisor', 'Admin', 'Admin Utama', 'admin_utama'])) {
                return redirect()->route('admin.dashboard');
            } elseif ($userRole === 'Teller') {
                return redirect()->route('teller.dashboard');
            } elseif ($userRole === 'Saver') {
                return redirect()->route('saver.dashboard');
            }
        } elseif (session()->has('saver_id')) {
            return redirect()->route('saver.dashboard');
        }

        return view('auth.login');
    }

    // Memproses data form login (Pegawai & Nasabah Siswa)
    public function login(Request $request)
    {
        // 1. Validasi input
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Silakan masukkan Username, NIS, atau Nomor Rekening Anda.',
            'password.required' => 'Silakan masukkan Password atau PIN Keamanan Anda.',
        ]);

        $username = trim($request->username);
        $password = $request->password;
        $remember = $request->boolean('remember') && Schema::hasColumn('users', 'remember_token');

        // Mendukung login menggunakan email maupun username
        $loginField = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 2. Cek autentikasi utama via User model (Administrator, Teller, atau Siswa terdaftar)
        if (Auth::attempt([$loginField => $username, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            if (Auth::user()->status === 'Blokir') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'username' => 'Akun Anda sedang diblokir oleh Administrator.',
                ])->onlyInput('username');
            }

            // Ambil data role user yang sedang login
            $userRole = Auth::user()->role; 

            // Arahkan ke halaman sesuai dengan peran (role)
            switch ($userRole) {
                case 'Administrator':
                case 'Supervisor':
                case 'Admin':
                case 'Admin Utama':
                case 'admin_utama':
                    return redirect()->intended('/admin/dashboard');

                case 'Teller':
                    return redirect()->intended('/teller/dashboard');

                case 'Saver':
                    // Hubungkan dengan data akun Saver
                    $user = Auth::user();
                    $saver = Saver::where('username', $user->username)
                        ->orWhere('email', $user->email)
                        ->orWhere('student_id', str_replace('siswa_', '', $user->username))
                        ->first();

                    if (!$saver) {
                        $saver = Saver::where('student_name', $user->officer_name)->first();
                    }

                    if ($saver) {
                        if ($saver->status === 'inactive' || $saver->status === 'Blokir') {
                            Auth::logout();
                            $request->session()->invalidate();
                            $request->session()->regenerateToken();
                            return back()->withErrors([
                                'username' => 'Rekening tabungan Anda belum aktif atau sedang dinonaktifkan.',
                            ])->onlyInput('username');
                        }
                        session(['saver_id' => $saver->id, 'account_number' => $saver->account_number]);
                    }

                    return redirect()->intended('/nasabah/dashboard');

                default:
                    return redirect()->intended('/admin/dashboard'); 
            }
        }

        // 3. Fallback Khusus Nasabah / Siswa: Login menggunakan Nomor Rekening / NIS + Password / PIN 6-digit
        $saver = Saver::where(function($q) use ($username) {
            $q->where('account_number', $username)
              ->orWhere('student_id', $username)
              ->orWhere('username', $username)
              ->orWhere('email', $username);
        })->first();

        if ($saver) {
            $isValid = false;

            // Cek Password atau PIN Keamanan pada tabel savers
            if (!empty($saver->password) && Hash::check($password, $saver->password)) {
                $isValid = true;
            } elseif (!empty($saver->security_pin) && Hash::check($password, $saver->security_pin)) {
                $isValid = true;
            }

            // Cek juga akun User terkait jika ada
            $associatedUser = User::where('username', $saver->username)
                ->orWhere('username', 'siswa_' . $saver->student_id)
                ->orWhere('email', $saver->email)
                ->first();

            if (!$isValid && $associatedUser && Hash::check($password, $associatedUser->password)) {
                $isValid = true;
            }

            if ($isValid) {
                if ($saver->status === 'inactive' || $saver->status === 'Blokir') {
                    return back()->withErrors([
                        'username' => 'Rekening tabungan Anda belum aktif. Silakan lakukan aktivasi awal di loket Teller.',
                    ])->onlyInput('username');
                }

                $request->session()->regenerate();
                session(['saver_id' => $saver->id, 'account_number' => $saver->account_number]);

                // Login-kan juga ke session Auth jika ada User record
                if ($associatedUser) {
                    Auth::login($associatedUser, $remember);
                }

                return redirect()->intended('/nasabah/dashboard');
            }
        }

        // Jika kredensial tidak cocok
        return back()->withErrors([
            'username' => 'Username, NIS, No. Rekening, atau kata sandi/PIN yang dimasukkan salah.',
        ])->onlyInput('username');
    }

    // Logout untuk semua peran pengguna
    public function logout(Request $request)
    {
        Auth::logout();
        
        $request->session()->forget(['saver_id', 'account_number']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Anda telah berhasil keluar dari sistem.');
    }
}