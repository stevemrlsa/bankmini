@extends('layouts.app')

@section('title', 'Audit Log Sistem — Bank Mini')
@section('header_title', 'Audit Log Sistem')
@section('page-title', 'Audit Log Sistem')
@section('page-subtitle', 'Jejak aktivitas dan riwayat operasional sistem secara real-time')

@push('styles')
<style>
    .toolbar-card {
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 18px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 8px 22px rgba(22, 35, 63, 0.05);
    }

    .search-box { position: relative; max-width: 320px; width: 100%; }
    .search-box i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #8A93A6; font-size: 0.9rem; }
    .search-box input {
        width: 100%; padding: 9px 14px 9px 38px; border-radius: 12px;
        border: 1px solid rgba(22, 35, 63, 0.1); background: #fff; font-size: 0.85rem;
        font-family: 'Poppins', sans-serif; outline: none;
        transition: box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .search-box input:focus { border-color: #3B5BA5; box-shadow: 0 0 0 4px rgba(59, 91, 165, 0.1); }

    .filter-select {
        border-radius: 12px; border: 1px solid rgba(22, 35, 63, 0.1);
        padding: 9px 14px; font-size: 0.83rem; font-family: 'Poppins', sans-serif;
        background: #fff; color: #5C6579; outline: none;
    }

    .readonly-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        background: rgba(180, 83, 9, 0.1);
        color: #B45309;
        font-size: 0.76rem;
        font-weight: 500;
        padding: 8px 14px;
        border-radius: 12px;
    }

    .table-premium-wrap {
        border-radius: 20px; overflow: hidden; background: #fff;
        border: 1px solid rgba(22, 35, 63, 0.07); box-shadow: 0 10px 26px rgba(22, 35, 63, 0.05);
    }

    table.table-premium { margin: 0; font-size: 0.85rem; }
    table.table-premium thead th {
        background: #F7F9F9; color: #5C6579; font-weight: 600; font-size: 0.74rem;
        border: none; padding: 14px 20px; white-space: nowrap; letter-spacing: 0.04em;
    }
    table.table-premium tbody td {
        padding: 14px 20px; vertical-align: middle;
        border-top: 1px solid rgba(22, 35, 63, 0.06); color: #1C2536;
    }
    table.table-premium tbody tr:hover { background: rgba(59, 91, 165, 0.03); }

    .timestamp-cell {
        white-space: nowrap;
        color: #5C6579;
        font-size: 0.8rem;
    }

    .timestamp-cell strong {
        display: block;
        color: #1C2536;
        font-size: 0.83rem;
        font-weight: 600;
    }

    .actor-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .actor-avatar {
        width: 34px; 
        height: 34px; 
        border-radius: 50%;
        background: #EFF6FF;
        color: #1E3A8A; 
        border: 1px solid #DBEAFE;
        display: flex; 
        align-items: center; 
        justify-content: center;
        font-size: 0.82rem; 
        font-weight: 700; 
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(30, 58, 138, 0.08);
    }

    .action-badge {
        font-size: 0.72rem;
        font-weight: 600;
        padding: 5px 11px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .action-badge.setoran   { background: #DCFCE7; color: #15803D; }
    .action-badge.penarikan { background: #FEF3C7; color: #B45309; }
    .action-badge.aktivasi  { background: #CCFBF1; color: #0F766E; }
    .action-badge.shift     { background: #E0E7FF; color: #4338CA; }
    .action-badge.approval  { background: #DCFCE7; color: #15803D; }
    .action-badge.reject    { background: #FEE2E2; color: #B91C1C; }
    .action-badge.auth      { background: #F3F4F6; color: #4B5563; }

    .module-text {
        font-size: 0.8rem;
        color: #5C6579;
        font-weight: 500;
    }

    .detail-text {
        font-size: 0.82rem;
        color: #1C2536;
        max-width: 360px;
        line-height: 1.4;
    }

    .ip-text {
        font-family: monospace;
        font-size: 0.78rem;
        color: #8A93A6;
    }

    .pagination-wrap {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; font-size: 0.8rem; color: #808C9E; border-top: 1px solid rgba(22, 35, 63, 0.06);
    }
</style>
@endpush

@section('content')

    <!-- Toolbar Filter -->
    <div class="toolbar-card">
        <form action="{{ route('admin.audit.index') }}" method="GET" class="d-flex justify-content-between align-items-center gap-3 flex-wrap w-100">
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari pelaku, modul, aksi, atau IP...">
                </div>

                <select name="action" class="filter-select" onchange="this.form.submit()">
                    <option value="all" {{ request('action', 'all') === 'all' ? 'selected' : '' }}>Semua Kategori Aksi</option>
                    <option value="transaksi" {{ request('action') === 'transaksi' ? 'selected' : '' }}>Transaksi (Setor, Tarik, Aktivasi)</option>
                    <option value="shift" {{ request('action') === 'shift' ? 'selected' : '' }}>Shift Kasir (Buka, Tambah, Tutup)</option>
                    <option value="approval" {{ request('action') === 'approval' ? 'selected' : '' }}>Approval Kas (Setujui / Tolak)</option>
                    <option value="login" {{ request('action') === 'login' ? 'selected' : '' }}>Autentikasi (Login / Logout)</option>
                </select>

                <select name="range" class="filter-select" onchange="this.form.submit()">
                    <option value="7days" {{ request('range', '7days') === '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30days" {{ request('range') === '30days' ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="this_month" {{ request('range') === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="all" {{ request('range') === 'all' ? 'selected' : '' }}>Semua Riwayat</option>
                </select>

                @if(request('search') || (request('action') && request('action') !== 'all') || (request('range') && request('range') !== '7days'))
                    <a href="{{ route('admin.audit.index') }}" class="btn btn-outline-secondary rounded-pill px-3 py-1 btn-sm" title="Reset Filter">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                @endif
            </div>

            <span class="readonly-chip"><i class="bi bi-shield-lock-fill"></i> Log Terenkripsi &amp; Hanya-Baca</span>
        </form>
    </div>

    <!-- Tabel Audit -->
    <div class="table-premium-wrap">
        <div class="table-responsive">
            <table class="table table-premium">
                <thead>
                    <tr>
                        <th>Waktu (WIB)</th>
                        <th>Pelaku</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Detail Aktivitas</th>
                        <th>Alamat IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="timestamp-cell">
                            <strong>{{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y') : '-' }}</strong>
                            {{ $log->created_at ? $log->created_at->timezone('Asia/Jakarta')->format('H:i:s') . ' WIB' : '-' }}
                        </td>
                        <td>
                            <div class="actor-cell">
                                <span class="actor-avatar">{{ strtoupper(substr($log->actor_name ?? $log->user->officer_name ?? $log->user->username ?? 'S', 0, 1)) }}</span>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $log->actor_name ?? $log->user->officer_name ?? $log->user->username ?? 'Sistem' }}</div>
                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $log->user->role ?? 'Petugas' }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            @php
                                $act = strtoupper($log->action ?? '');
                            @endphp
                            @if(in_array($act, ['SETORAN_TUNAI']))
                                <span class="action-badge setoran"><i class="bi bi-arrow-down-circle-fill"></i> Setoran Tunai</span>
                            @elseif(in_array($act, ['PENARIKAN_TUNAI']))
                                <span class="action-badge penarikan"><i class="bi bi-arrow-up-circle-fill"></i> Penarikan Tunai</span>
                            @elseif(in_array($act, ['AKTIVASI_REKENING']))
                                <span class="action-badge aktivasi"><i class="bi bi-person-check-fill"></i> Aktivasi Akun</span>
                            @elseif(in_array($act, ['BUKA_LACI_SHIFT']))
                                <span class="action-badge shift"><i class="bi bi-cash-coin"></i> Buka Laci</span>
                            @elseif(in_array($act, ['TAMBAH_KAS_LACI']))
                                <span class="action-badge shift"><i class="bi bi-plus-circle-fill"></i> Tambah Kas</span>
                            @elseif(in_array($act, ['TUTUP_SHIFT_KAS', 'TUTUP_SHIFT_OTOMATIS']))
                                <span class="action-badge shift"><i class="bi bi-journal-check"></i> Tutup Shift</span>
                            @elseif(in_array($act, ['APPROVE_KAS']))
                                <span class="action-badge approval"><i class="bi bi-shield-check"></i> Otorisasi Kas</span>
                            @elseif(in_array($act, ['REJECT_KAS']))
                                <span class="action-badge reject"><i class="bi bi-x-circle-fill"></i> Tolak Laporan</span>
                            @elseif(in_array($act, ['LOGIN']))
                                <span class="action-badge auth"><i class="bi bi-box-arrow-in-right"></i> Login</span>
                            @elseif(in_array($act, ['LOGOUT']))
                                <span class="action-badge auth"><i class="bi bi-box-arrow-right"></i> Logout</span>
                            @else
                                <span class="action-badge auth"><i class="bi bi-activity"></i> {{ $log->action }}</span>
                            @endif
                        </td>
                        <td class="module-text">{{ $log->module ?? 'Sistem' }}</td>
                        <td class="detail-text">{{ $log->detail }}</td>
                        <td class="ip-text">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                            <span class="fw-semibold">Belum ada riwayat aktivitas yang tercatat sesuai kriteria.</span>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-wrap">
            <span class="small text-muted">Menampilkan {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} dari {{ $logs->total() }} aktivitas</span>
            <div>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

@endsection
