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
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $hasActiveShift = TellerShift::where('user_id', auth()->id())
            ->where('status', 'Open')
            ->exists();

        if (!$hasActiveShift) {
            return redirect()->route('teller.shift.create')
                ->with('warning', 'Anda harus memasukkan Modal Awal (Buka Laci) sebelum memulai transaksi.');
        }

        return $next($request);
    }
}
