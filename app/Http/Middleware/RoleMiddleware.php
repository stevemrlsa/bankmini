<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Autentikasi gagal : Silahkan login terlebih dahulu.'
                ], 401);
            }
            return redirect('/login')->with('error', 'Silahkan login terlebih dahulu.');
        }

        $userRole = Auth::user()->role;

        // Build list of acceptable roles, handling Admin/Administrator equivalency
        $allowedRoles = [];
        foreach ($roles as $role) {
            $subRoles = explode(',', $role);
            foreach ($subRoles as $r) {
                $r = trim($r);
                $allowedRoles[] = $r;
                if (in_array(strtolower($r), ['admin', 'administrator'])) {
                    $allowedRoles[] = 'Admin';
                    $allowedRoles[] = 'Administrator';
                    $allowedRoles[] = 'Admin Utama';
                    $allowedRoles[] = 'admin_utama';
                    $allowedRoles[] = 'Supervisor';
                }
            }
        }

        if (in_array($userRole, $allowedRoles, true)) {
            return $next($request);
        }

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Akses diblokir : Anda tidak memiliki hak akses ke endpoint ini.'
            ], 403);
        }

        // If authenticated user does not have permission, redirect to their dashboard instead of wiping session
        if (in_array($userRole, ['Administrator', 'Admin', 'Supervisor', 'Admin Utama', 'admin_utama'])) {
            return redirect()->route('admin.dashboard')->with('error', 'Akses dibatasi untuk menu tersebut.');
        } elseif ($userRole === 'Teller') {
            return redirect()->route('teller.dashboard')->with('error', 'Akses dibatasi untuk menu tersebut.');
        } elseif ($userRole === 'Saver') {
            return redirect()->route('saver.dashboard')->with('error', 'Akses dibatasi untuk menu tersebut.');
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('error', 'Akses diblokir : Anda mencoba mengakses halaman yang tidak diizinkan.');
    }
}
