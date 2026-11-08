@extends('layouts.app')

@section('header_title', 'Riwayat Seluruh Transaksi')
@section('page-title', 'Riwayat Seluruh Transaksi')
@section('page-subtitle', 'Daftar lengkap transaksi setoran dan penarikan nasabah di seluruh loket Teller')

@section('content')
<div class="container-fluid px-0">

    {{-- Ringkasan Statistik Transaksi --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 18px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: linear-gradient(135deg, #6D28D9, #9F7AEA);">
                        <i class="bi bi-arrow-left-right fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Total Transaksi Ditampilkan</small>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($totalCountFiltered ?? 0) }} Transaksi</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 18px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: linear-gradient(135deg, #0F766E, #14B8A6);">
                        <i class="bi bi-arrow-down-left fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Total Setoran Masuk</small>
                        <h5 class="fw-bold text-success mb-0">+Rp {{ number_format($totalSetoranFiltered ?? 0, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-3" style="border-radius: 18px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 44px; height: 44px; background: linear-gradient(135deg, #B45309, #F59E0B);">
                        <i class="bi bi-arrow-up-right fs-5"></i>
                    </div>
                    <div>
                        <small class="text-muted fw-semibold d-block">Total Penarikan Keluar</small>
                        <h5 class="fw-bold text-danger mb-0">-Rp {{ number_format($totalPenarikanFiltered ?? 0, 0, ',', '.') }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
        {{-- Filter Form --}}
        <form action="{{ route('admin.transactions.index') }}" method="GET" class="row g-2 align-items-center mb-4">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama Siswa, NIS, atau No. Rekening..." value="{{ request('search') }}" style="border-radius: 0 12px 12px 0;">
                </div>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select" style="border-radius: 12px;" onchange="this.form.submit()">
                    <option value="">Semua Jenis Transaksi</option>
                    <option value="deposit" {{ request('type') === 'deposit' ? 'selected' : '' }}>Setoran Tunai (Deposit)</option>
                    <option value="withdrawal" {{ request('type') === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai (Withdrawal)</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" style="border-radius: 12px;" value="{{ request('date') }}" onchange="this.form.submit()">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark rounded-pill px-3 flex-fill" style="background: #16233F;">Filter</button>
                @if(request('search') || request('type') || request('date'))
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-outline-secondary rounded-pill px-3">Reset</a>
                @endif
            </div>
        </form>

        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID / Waktu</th>
                        <th>No. Rekening &amp; NIS</th>
                        <th>Nama Nasabah</th>
                        <th>Kelas</th>
                        <th>Jenis Transaksi</th>
                        <th>Petugas Teller</th>
                        <th class="text-end">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $isDeposit = in_array(strtolower($tx->transaction_type), ['deposit', 'setor', 'setoran']);
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-bold text-dark font-monospace">#TRX-{{ $tx->id }}</div>
                                <small class="text-muted">{{ $tx->created_at ? $tx->created_at->timezone('Asia/Jakarta')->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark font-monospace">{{ $tx->saver->account_number ?? '-' }}</div>
                                <small class="text-muted">NIS: {{ $tx->saver->student_id ?? '-' }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold text-dark">{{ $tx->saver->student_name ?? $tx->saver->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1">{{ $tx->saver->grade_class ?? '-' }}</span>
                            </td>
                            <td>
                                @if($isDeposit)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-arrow-down-left me-1"></i> Setoran Tunai
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                        <i class="bi bi-arrow-up-right me-1"></i> Penarikan Tunai
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-secondary fw-medium">
                                    <i class="bi bi-person-badge me-1"></i>{{ $tx->user->officer_name ?? $tx->user->username ?? 'Teller' }}
                                </span>
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $isDeposit ? 'text-success' : 'text-danger' }}">
                                    {{ $isDeposit ? '+Rp ' : '-Rp ' }}{{ number_format($tx->amount ?? 0, 0, ',', '.') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox display-6 d-block mb-2 opacity-50"></i>
                                Belum ada data transaksi yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($transactions, 'hasPages') && $transactions->hasPages())
            <div class="d-flex justify-content-between align-items-center pt-3 mt-3 border-top small text-muted flex-wrap gap-2">
                <span>Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} dari {{ $transactions->total() }} transaksi</span>
                <div>{{ $transactions->links() }}</div>
            </div>
        @endif
    </div>
</div>
@endsection

