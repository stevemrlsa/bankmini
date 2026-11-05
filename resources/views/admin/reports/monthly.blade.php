@extends('layouts.app')

@section('title', 'Laporan Bulanan Transaksi')
@section('header_title', 'Laporan Bulanan')
@section('page-title', 'Laporan Bulanan Transaksi')

@section('content')
<div class="card-glass mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-calendar3-range text-primary me-2"></i>Laporan Transaksi Bulanan</h5>
            <small class="text-muted">Rekapitulasi seluruh aktivitas transaksi setoran, penarikan, dan kas internal</small>
        </div>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.reports.monthly.pdf', ['month' => $month, 'year' => $year]) }}" class="btn btn-danger rounded-pill px-3 shadow-sm">
                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak / Unduh PDF
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <form method="GET" action="{{ route('admin.reports.monthly') }}" class="row g-2 align-items-center mt-3 pt-3 border-top">
        <div class="col-auto">
            <label class="col-form-label fw-semibold small text-dark">Pilih Periode:</label>
        </div>
        <div class="col-auto">
            <select name="month" class="form-select form-select-sm rounded-pill" style="min-width: 150px; padding-left: 16px; padding-right: 36px !important; background-position: right 14px center; font-weight: 500;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ (int)$month === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <select name="year" class="form-select form-select-sm rounded-pill" style="min-width: 110px; padding-left: 16px; padding-right: 36px !important; background-position: right 14px center; font-weight: 500;">
                @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                    <option value="{{ $y }}" {{ (int)$year === $y ? 'selected' : '' }}>
                        {{ $y }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold shadow-sm" style="background: linear-gradient(135deg, #16233F, #0E1830); border:none; padding-top: 6px; padding-bottom: 6px;">
                <i class="bi bi-filter"></i> Terapkan
            </button>
        </div>
    </form>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-glass p-3 h-100">
            <span class="text-muted small d-block mb-1">Total Setoran (Cash-In)</span>
            <span class="fs-5 fw-bold text-success">+ Rp {{ number_format($totalDeposits, 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-glass p-3 h-100">
            <span class="text-muted small d-block mb-1">Total Penarikan (Cash-Out)</span>
            <span class="fs-5 fw-bold text-danger">- Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-glass p-3 h-100">
            <span class="text-muted small d-block mb-1">Net Tabungan Siswa</span>
            <span class="fs-5 fw-bold {{ $netSavings >= 0 ? 'text-primary' : 'text-danger' }}">
                Rp {{ number_format($netSavings, 0, ',', '.') }}
            </span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-glass p-3 h-100">
            <span class="text-muted small d-block mb-1">Pindahan ke Brankas (Vault)</span>
            <span class="fs-5 fw-bold text-dark">Rp {{ number_format($totalVault, 0, ',', '.') }}</span>
        </div>
    </div>
</div>

<!-- Tabular Report -->
<div class="card-glass">
    <div class="table-premium-wrap">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                <thead style="background: #F8FAFC;">
                    <tr>
                        <th class="py-3 px-3">No</th>
                        <th class="py-3">Waktu Transaksi</th>
                        <th class="py-3">No. Rekening</th>
                        <th class="py-3">Nama Nasabah</th>
                        <th class="py-3">Tipe Transaksi</th>
                        <th class="py-3 text-end">Nominal (Rp)</th>
                        <th class="py-3">Petugas</th>
                        <th class="py-3 text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $index => $trx)
                    <tr>
                        <td class="px-3">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->translatedFormat('d M Y H:i') }}</td>
                        <td>
                            @if($trx->saver)
                                <code>{{ $trx->saver->account_number }}</code>
                            @else
                                <span class="text-muted">Internal Brankas</span>
                            @endif
                        </td>
                        <td>
                            @if($trx->saver)
                                <span class="fw-semibold text-dark">{{ $trx->saver->student_name }}</span>
                            @else
                                <span class="text-muted">Bank Mini Kas Pusat</span>
                            @endif
                        </td>
                        <td>
                            @if($trx->transaction_type === 'Deposit')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Setoran</span>
                            @elseif($trx->transaction_type === 'Withdrawal')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2">Penarikan</span>
                            @else
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2">Vault Transfer</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold {{ $trx->transaction_type === 'Deposit' ? 'text-success' : ($trx->transaction_type === 'Withdrawal' ? 'text-danger' : 'text-dark') }}">
                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                        </td>
                        <td>{{ $trx->user->officer_name ?? $trx->user->username ?? '-' }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border rounded-pill px-2">{{ $trx->status }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada transaksi pada periode bulan ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
