<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Carbon\Carbon;

class AuditController extends Controller
{
    /**
     * Display a listing of real-time system audit logs.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->orderBy('created_at', 'desc');

        // Filter: Pencarian teks (Pelaku, Modul, Detail, atau IP Address)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('actor_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%")
                  ->orWhere('module', 'like', "%{$search}%")
                  ->orWhere('detail', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        // Filter: Kategori Aksi
        if ($request->filled('action') && $request->action !== 'all') {
            $action = $request->action;
            if ($action === 'login') {
                $query->whereIn('action', ['LOGIN', 'LOGOUT']);
            } elseif ($action === 'transaksi') {
                $query->whereIn('action', ['SETORAN_TUNAI', 'PENARIKAN_TUNAI', 'AKTIVASI_REKENING']);
            } elseif ($action === 'shift') {
                $query->whereIn('action', ['BUKA_LACI_SHIFT', 'TAMBAH_KAS_LACI', 'TUTUP_SHIFT_KAS']);
            } elseif ($action === 'approval') {
                $query->whereIn('action', ['APPROVE_KAS', 'REJECT_KAS']);
            } else {
                $query->where('action', 'like', "%{$action}%");
            }
        }

        // Filter: Rentang Waktu (Default: 7 Hari Terakhir)
        $range = $request->input('range', '7days');
        if ($range === '7days') {
            $query->where('created_at', '>=', Carbon::now('Asia/Jakarta')->subDays(7)->startOfDay());
        } elseif ($range === '30days') {
            $query->where('created_at', '>=', Carbon::now('Asia/Jakarta')->subDays(30)->startOfDay());
        } elseif ($range === 'this_month') {
            $query->where('created_at', '>=', Carbon::now('Asia/Jakarta')->startOfMonth());
        }

        $logs = $query->paginate(15)->withQueryString();

        return view('admin.audit.index', compact('logs'));
    }
}
