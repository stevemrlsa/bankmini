<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\TellerShift;

class EnsureTellerShiftOpen
{
    /**
     * Handle an incoming request.
     * Memastikan terdapat laci kasir / shift yang sedang aktif (Open).
     * Jika sudah ada shift aktif yang dibuka oleh teller lain (Shift Bersama),
     * teller saat ini otomatis terhubung tanpa harus menginput modal awal kembali.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $activeShift = TellerShift::getActiveOpenShift();

        if (!$activeShift) {
            return redirect()->route('teller.shift.create')
                ->with('warning', 'Anda harus memasukkan Modal Awal (Buka Laci) sebelum memulai transaksi.');
        }

        // Jika shift dibuka oleh teller lain, daftarkan teller saat ini ke shift bersama
        if (auth()->check() && auth()->user()->role === 'Teller') {
            $activeShift->attachTeller(auth()->id());
        }

        return $next($request);
    }
}
