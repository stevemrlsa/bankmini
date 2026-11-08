<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\Saver;

class CheckSaverSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Jika sesi saver_id belum ada namun pengguna sudah terotentikasi sebagai Saver di Auth::user()
        if (!Session::has('saver_id') && Auth::check() && Auth::user()->role === 'Saver') {
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
            }
        }

        // 2. Jika tetap tidak memiliki saver_id, arahkan ke form login utama
        if (!Session::has('saver_id')) {
            return redirect()->route('login')
                             ->with('error', 'Silakan login terlebih dahulu untuk mengakses portal tabungan digital.');
        }

        // 3. Cek apakah rekening nasabah diblokir
        $currentSaver = Saver::find(Session::get('saver_id'));
        if ($currentSaver && $currentSaver->status === 'blocked') {
            Session::forget(['saver_id', 'account_number']);
            Auth::logout();
            return redirect()->route('login')
                             ->with('error', 'Akses Ditolak: Rekening nasabah Anda sedang DIBLOKIR oleh Administrator. Silakan hubungi petugas loket Bank Mini.');
        }

        return $next($request);
    }
}
