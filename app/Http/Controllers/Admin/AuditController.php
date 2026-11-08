<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditController extends Controller
{
    /**
     * Display a listing of real-time system audit logs.
     */
    public function index(Request $request)
    {
        // Fail-safe: Pastikan tabel audit_logs sudah terbentuk di database
        if (!Schema::hasTable('audit_logs')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {}

            if (!Schema::hasTable('audit_logs')) {
                try {
                    Schema::create('audit_logs', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                        $table->string('actor_name')->default('Sistem');
                        $table->string('action')->default('general');
                        $table->string('module')->default('Umum');
                        $table->text('detail');
                        $table->string('ip_address', 45)->nullable();
                        $table->text('user_agent')->nullable();
                        $table->timestamps();

                        $table->index('action');
                        $table->index('module');
                        $table->index('created_at');
                    });
                } catch (\Throwable $e) {
                    $logs = new LengthAwarePaginator([], 0, 15);
                    return view('admin.audit.index', compact('logs'));
                }
            }
        }

        try {
            // Jika log masih kosong sama sekali, buat entri inisialisasi awal
            if (AuditLog::count() === 0) {
                AuditLog::record(
                    'general',
                    'Sistem',
                    'Inisialisasi tabel Audit Log Sistem berhasil diaktifkan secara real-time.',
                    auth()->id(),
                    auth()->user()?->officer_name ?? auth()->user()?->username ?? 'Sistem Otomatis'
                );
            }

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
                    $query->whereIn('action', ['BUKA_LACI_SHIFT', 'TAMBAH_KAS_LACI', 'TUTUP_SHIFT_KAS', 'TUTUP_SHIFT_OTOMATIS']);
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
        } catch (\Throwable $e) {
            $logs = new LengthAwarePaginator([], 0, 15);
        }

        return view('admin.audit.index', compact('logs'));
    }
}
