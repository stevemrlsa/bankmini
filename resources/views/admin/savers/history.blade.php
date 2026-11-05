@extends('layouts.app')

@section('header_title', 'Riwayat Mutasi Nasabah')
@section('page-title', 'Riwayat Mutasi Nasabah')
@section('page-subtitle', 'Histori lengkap setoran dan penarikan tabungan siswa')

@section('content')

    <div class="mb-3">
        <a href="{{ route('admin.savers.index') }}" class="btn btn-sm btn-link text-decoration-none text-muted p-0 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Data Nasabah
        </a>
    </div>

    <!-- Ringkasan Profil Siswa -->
    <div class="card-glass mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 mb-2 fw-semibold">
                    <i class="bi bi-check-circle-fill me-1"></i> Rekening Aktif
                </span>
                <h4 class="fw-bold text-dark mb-1">{{ $saver->student_name }}</h4>
                <div class="text-muted small">
                    <span>No. Rekening: <strong class="text-dark">{{ $saver->account_number ?? '-' }}</strong></span>
                    <span class="mx-2">·</span>
                    <span>NIS: <strong class="text-dark">{{ $saver->student_id }}</strong></span>
                    <span class="mx-2">·</span>
                    <span>Kelas: <strong class="text-dark">{{ $saver->grade_class ?? '-' }}</strong></span>
                </div>
            </div>
            <div class="text-end">
                <span class="text-muted small d-block">Saldo Tabungan Saat Ini</span>
                <span class="fs-3 fw-bold text-success">Rp {{ number_format($saver->saldo ?? 0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- Tabel Mutasi Transaksi -->
    <div class="table-premium-wrap">
        <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white">
            <h6 class="fw-bold text-dark mb-0">Daftar Mutasi Transaksi</h6>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak Mutasi
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-premium mb-0">
                <thead>
                    <tr>
                        <th>Waktu Transaksi</th>
                        <th>Kode Trx</th>
                        <th>Petugas Teller</th>
                        <th>Jenis Mutasi</th>
                        <th>Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        <tr>
                            <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="fw-semibold">#TRX-{{ $tx->id }}</span></td>
                            <td>{{ $tx->teller->officer_name ?? $tx->teller->username ?? 'Teller' }}</td>
                            <td>
                                @if(strtolower($tx->transaction_type) == 'deposit')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        <i class="bi bi-arrow-down-left me-1"></i> Setoran Tunai
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                        <i class="bi bi-arrow-up-right me-1"></i> Penarikan Tunai
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="fw-bold {{ strtolower($tx->transaction_type) == 'deposit' ? 'text-success' : 'text-danger' }}">
                                    {{ strtolower($tx->transaction_type) == 'deposit' ? '+ ' : '- ' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $tx->status ?? 'Approved' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat mutasi transaksi untuk nasabah ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-3 bg-white border-top">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

@endsection
