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
                Auth::logout();
            }
        }
        
        if (session()->has('saver_id')) {
            session()->forget(['saver_id', 'account_number']);
        }

        return view('auth.login');
    }

    // Memproses data form login (Khusus Pegawai: Administrator & Teller)
    public function login(Request $request)
    {
        // 1. Validasi input (hanya username & password untuk pegawai)
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Silakan masukkan Username Anda.',
            'password.required' => 'Silakan masukkan Password Anda.',
        ]);

        $username = trim($request->username);
        $password = $request->password;
        $remember = $request->boolean('remember') && Schema::hasColumn('users', 'remember_token');

        // Mendukung login menggunakan email maupun username
        $loginField = filter_var($username, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // 2. Cek autentikasi utama via User model (Administrator atau Teller)
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

            // Tolak akses jika peran adalah Saver (Nasabah)
            if ($userRole === 'Saver') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors([
                    'username' => 'Akses login web hanya diperuntukkan bagi Admin & Teller. Nasabah dianjurkan untuk menggunakan Aplikasi Mobile (APK) Bank Mini.',
                ])->onlyInput('username');
            }

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

                default:
                    return redirect()->intended('/admin/dashboard'); 
            }
        }

        // 3. Jika pengguna mencoba login dengan data akun Nasabah/Siswa (Nomor Rekening, NIS, atau Username Saver)
        $saver = Saver::where(function($q) use ($username) {
            $q->where('account_number', $username)
              ->orWhere('student_id', $username)
              ->orWhere('username', $username)
              ->orWhere('email', $username);
        })->first();

        if ($saver) {
            return back()->withErrors([
                'username' => 'Akses login web hanya diperuntukkan bagi Admin & Teller. Nasabah dianjurkan untuk menggunakan Aplikasi Mobile (APK) Bank Mini.',
            ])->onlyInput('username');
        }

        // Jika kredensial tidak cocok
        return back()->withErrors([
            'username' => 'Username atau password yang Anda masukkan salah.',
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