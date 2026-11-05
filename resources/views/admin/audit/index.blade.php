@extends('layouts.app')

@section('title', 'Audit Jurnal — Bank Mini')
@section('header_title', 'Audit Jurnal')
@section('page-title', 'Audit Jurnal')
@section('page-subtitle', 'Riwayat perubahan sistem — hanya baca')

@push('styles')
<style>
    .toolbar-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 18px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        box-shadow: 0 8px 22px rgba(22, 35, 63, 0.05);
    }

    .search-box { position: relative; max-width: 300px; width: 100%; }
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
        background: #fff; color: #5C6579;
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
        background: #F7F9F9; color: #5C6579; font-weight: 500; font-size: 0.74rem;
        border: none; padding: 14px 20px; white-space: nowrap;
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
        font-weight: 500;
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

    .action-tag {
        font-size: 0.72rem;
        font-weight: 500;
        padding: 5px 11px;
        border-radius: 20px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .action-tag.create, .action-tag.store { background: rgba(22, 163, 74, 0.1); color: #15803D; }
    .action-tag.update { background: rgba(59, 91, 165, 0.1); color: #3B5BA5; }
    .action-tag.delete, .action-tag.destroy { background: rgba(220, 38, 38, 0.09); color: #B91C1C; }
    .action-tag.login  { background: rgba(107, 114, 128, 0.1); color: #4B5563; }

    .module-text {
        font-size: 0.8rem;
        color: #5C6579;
    }

    .detail-text {
        font-size: 0.82rem;
        color: #1C2536;
        max-width: 320px;
    }

    .ip-text {
        font-family: 'Poppins', sans-serif;
        font-size: 0.76rem;
        color: #A2AABB;
    }

    .pagination-note {
        display: flex; align-items: center; justify-content: space-between;
        padding: 16px 20px; font-size: 0.78rem; color: #808C9E;
    }
</style>
@endpush

@section('content')

    <!-- Toolbar -->
    <div class="toolbar-card">
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <div class="search-box">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Cari berdasarkan pelaku atau modul...">
            </div>
            <select class="filter-select">
                <option selected>Semua Aksi</option>
                <option>Tambah Data</option>
                <option>Ubah Data</option>
                <option>Hapus Data</option>
                <option>Login Sistem</option>
            </select>
            <select class="filter-select">
                <option selected>7 Hari Terakhir</option>
                <option>30 Hari Terakhir</option>
                <option>Bulan Ini</option>
            </select>
        </div>
        <span class="readonly-chip"><i class="bi bi-shield-lock-fill"></i> Mode hanya-baca</span>
    </div>

    <!-- Tabel Audit -->
    <div class="table-premium-wrap">
        <div class="table-responsive">
            <table class="table table-premium">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Pelaku</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Detail Perubahan</th>
                        <th>Alamat IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs ?? [1,2,3,4,5] as $index => $log)
                    <tr>
                        <td class="timestamp-cell">
                            <strong>{{ isset($log->created_at) && is_object($log->created_at) ? $log->created_at->format('d M Y') : '05 Sep 2026' }}</strong>
                            {{ isset($log->created_at) && is_object($log->created_at) ? $log->created_at->format('H:i:s') : '10:42:1' . $index }}
                        </td>
                        <td>
                            <div class="actor-cell">
                                <span class="actor-avatar">{{ strtoupper(substr($log->actor ?? $log->user->name ?? 'Ahmad Fauzi', 0, 1)) }}</span>
                                {{ $log->actor ?? $log->user->name ?? 'Ahmad Fauzi' }}
                            </div>
                        </td>
                        <td>
                            @php $tipe = strtolower($log->action ?? $log->action_type ?? ['create','update','delete','login','update'][$index % 5]); @endphp
                            <span class="action-tag {{ $tipe }}">
                                @if(in_array($tipe, ['create', 'store'])) <i class="bi bi-plus-circle-fill"></i> Tambah
                                @elseif($tipe === 'update') <i class="bi bi-pencil-fill"></i> Ubah
                                @elseif(in_array($tipe, ['delete', 'destroy'])) <i class="bi bi-trash-fill"></i> Hapus
                                @else <i class="bi bi-box-arrow-in-right"></i> {{ ucfirst($tipe) }}
                                @endif
                            </span>
                        </td>
                        <td class="module-text">{{ $log->module ?? $log->model_type ?? 'Transaksi Tabungan' }}</td>
                        <td class="detail-text">{{ $log->detail ?? $log->description ?? 'Setoran nasabah 1001-0004 sebesar Rp 25.000 berhasil dicatat.' }}</td>
                        <td class="ip-text">{{ $log->ip_address ?? '192.168.1.24' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada aktivitas yang tercatat.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-note">
            @if(isset($logs) && method_exists($logs, 'links'))
                {{ $logs->links() }}
            @endif
        </div>
    </div>

@endsection