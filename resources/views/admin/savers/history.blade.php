@extends('layouts.app')

@section('header_title', 'Riwayat Mutasi Nasabah')
@section('page-title', 'Riwayat Mutasi Nasabah')
@section('page-subtitle', 'Histori lengkap setoran dan penarikan tabungan siswa serta cetak Rekening Koran PDF')

@section('content')
@push('styles')
<style>
    .btn.btn-sm {
        padding: 6px 14px;
        font-size: 0.8rem;
    }
</style>
@endpush
@php
    $activePeriod = $periodData['period'] ?? 'all';
    $pdfParams = [
        'period'     => $activePeriod,
        'type'       => $normalizedType ?? 'all',
        'start_date' => $periodData['start_date'] ?? '',
        'end_date'   => $periodData['end_date'] ?? '',
    ];
@endphp

    <div class="mb-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <a href="{{ route('admin.savers.index') }}" class="btn btn-sm btn-link text-decoration-none text-muted p-0 fw-semibold">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Data Nasabah
        </a>
    </div>

    <!-- Ringkasan Profil Siswa -->
    <div class="card-glass mb-4">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                @if($saver->trashed())
                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 mb-2 fw-semibold">
                        <i class="bi bi-archive-fill me-1"></i> Rekening Diarsipkan
                    </span>
                @elseif($saver->status === 'blocked')
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 mb-2 fw-semibold">
                        <i class="bi bi-lock-fill me-1"></i> Rekening Diblokir
                    </span>
                @else
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 mb-2 fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i> Rekening Aktif
                    </span>
                @endif

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

    <!-- Filter Periode & Ringkasan Mutasi -->
    <div class="card-glass mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="small fw-semibold text-muted me-1">Periode Mutasi:</span>

                <a href="{{ route('admin.savers.history', ['id' => $saver->id, 'period' => 'all', 'type' => $normalizedType]) }}"
                   class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'all' ? 'btn-dark' : 'btn-outline-secondary' }}"
                   style="{{ $activePeriod === 'all' ? 'background: #16233F; border-color: #16233F;' : '' }}">
                    Semua Riwayat
                </a>

                <a href="{{ route('admin.savers.history', ['id' => $saver->id, 'period' => 'this_month', 'type' => $normalizedType]) }}"
                   class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'this_month' ? 'btn-dark' : 'btn-outline-secondary' }}"
                   style="{{ $activePeriod === 'this_month' ? 'background: #16233F; border-color: #16233F;' : '' }}">
                    Bulan Ini
                </a>

                <a href="{{ route('admin.savers.history', ['id' => $saver->id, 'period' => 'last_3_months', 'type' => $normalizedType]) }}"
                   class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'last_3_months' ? 'btn-dark' : 'btn-outline-secondary' }}"
                   style="{{ $activePeriod === 'last_3_months' ? 'background: #16233F; border-color: #16233F;' : '' }}">
                    3 Bulan Terakhir
                </a>

                <button type="button"
                        id="btnToggleCustomerCustomPeriod"
                        class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'custom' ? 'btn-dark' : 'btn-outline-secondary' }}"
                        style="{{ $activePeriod === 'custom' ? 'background: #16233F; border-color: #16233F;' : '' }}">
                    Rentang Tanggal
                </button>
            </div>

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('admin.savers.history.pdf', array_merge(['id' => $saver->id], $pdfParams)) }}"
                   target="_blank"
                   class="btn btn-sm btn-danger rounded-pill px-3 fw-semibold shadow-sm">
                    <i class="bi bi-printer-fill me-1"></i> Preview / Cetak PDF
                </a>
                <a href="{{ route('admin.savers.history.pdf', array_merge(['id' => $saver->id, 'download' => 1], $pdfParams)) }}"
                   class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                    <i class="bi bi-download me-1"></i> Unduh PDF
                </a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.savers.history', $saver->id) }}" class="row g-2 align-items-end pt-3 border-top">
            <input type="hidden" name="period" id="inputCustomerPeriodMode" value="{{ $activePeriod }}">

            <div class="col-md-3 col-sm-6">
                <label class="form-label small fw-semibold text-muted mb-1">Jenis Mutasi</label>
                <select name="type" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                    <option value="all" {{ $normalizedType === 'all' ? 'selected' : '' }}>Semua Mutasi</option>
                    <option value="deposit" {{ $normalizedType === 'deposit' ? 'selected' : '' }}>Setoran Tunai (Deposit)</option>
                    <option value="withdrawal" {{ $normalizedType === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai (Withdrawal)</option>
                </select>
            </div>

            <div class="col-md-3 col-sm-6 customer-custom-date {{ $activePeriod === 'custom' ? '' : 'd-none' }}">
                <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" class="form-control form-control-sm rounded-3" value="{{ $periodData['start_date'] }}">
            </div>

            <div class="col-md-3 col-sm-6 customer-custom-date {{ $activePeriod === 'custom' ? '' : 'd-none' }}">
                <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control form-control-sm rounded-3" value="{{ $periodData['end_date'] }}">
            </div>

            <div class="col-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold"
                        style="background: linear-gradient(135deg, #16233F, #0E1830); border: none; padding-top: 6px; padding-bottom: 6px;">
                    <i class="bi bi-funnel-fill me-1"></i> Terapkan
                </button>
                @if($activePeriod !== 'all' || $normalizedType !== 'all')
                    <a href="{{ route('admin.savers.history', $saver->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        Reset
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Ringkasan Statistik Filter -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card-glass p-3 h-100 mb-0">
                <span class="text-muted small d-block mb-1">Frekuensi Mutasi</span>
                <span class="fs-5 fw-bold text-dark">{{ number_format($summary['total_count'], 0, ',', '.') }} Transaksi</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card-glass p-3 h-100 mb-0">
                <span class="text-muted small d-block mb-1">Total Kredit (Setoran)</span>
                <span class="fs-5 fw-bold text-success">+ Rp {{ number_format($summary['total_deposits'], 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card-glass p-3 h-100 mb-0">
                <span class="text-muted small d-block mb-1">Total Debet (Penarikan)</span>
                <span class="fs-5 fw-bold text-danger">- Rp {{ number_format($summary['total_withdrawals'], 0, ',', '.') }}</span>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card-glass p-3 h-100 mb-0">
                <span class="text-muted small d-block mb-1">Selisih Mutasi Periode</span>
                <span class="fs-5 fw-bold {{ $summary['net_change'] >= 0 ? 'text-primary' : 'text-danger' }}">
                    {{ $summary['net_change'] >= 0 ? '+ ' : '- ' }}Rp {{ number_format(abs($summary['net_change']), 0, ',', '.') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Tabel Mutasi Transaksi -->
    <div class="card-glass">
        <div class="pb-3 mb-2 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0">Daftar Mutasi Transaksi</h6>
                <small class="text-muted">Periode: {{ $periodData['label'] }} · {{ $typeLabel }} (Terbaru di atas)</small>
            </div>
            <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">
                PDF otomatis disusun format Rekening Koran (Saldo Berjalan)
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
                <thead>
                    <tr>
                        <th>Waktu Transaksi</th>
                        <th>Kode Trx</th>
                        <th>Petugas Teller</th>
                        <th>Jenis Mutasi</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                        @php
                            $isDep = \App\Services\MutationReportService::isDeposit($tx->transaction_type);
                            $txTime = \Carbon\Carbon::parse($tx->transaction_date ?? $tx->created_at)->timezone('Asia/Jakarta');
                        @endphp
                        <tr>
                            <td>{{ $txTime->format('d/m/Y H:i') }} WIB</td>
                            <td><span class="fw-semibold">#TRX-{{ $tx->id }}</span></td>
                            <td>{{ $tx->user->officer_name ?? $tx->user->username ?? 'Teller' }}</td>
                            <td>
                                @if($isDep)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                                        <i class="bi bi-arrow-down-left me-1"></i> Setoran Tunai
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">
                                        <i class="bi bi-arrow-up-right me-1"></i> Penarikan Tunai
                                    </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold {{ $isDep ? 'text-success' : 'text-danger' }}">
                                    {{ $isDep ? '+ ' : '- ' }}Rp {{ number_format($tx->amount, 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">
                                    {{ $tx->status ?? 'Approved' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat mutasi transaksi untuk filter yang dipilih.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="p-3 bg-white border-top">
                {{ $transactions->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    (function () {
        const btnToggle = document.getElementById('btnToggleCustomerCustomPeriod');
        const inputMode = document.getElementById('inputCustomerPeriodMode');
        const customFields = document.querySelectorAll('.customer-custom-date');

        if (btnToggle && inputMode) {
            btnToggle.addEventListener('click', function () {
                inputMode.value = 'custom';
                customFields.forEach(function (el) {
                    el.classList.remove('d-none');
                });
                btnToggle.classList.remove('btn-outline-secondary');
                btnToggle.classList.add('btn-dark');
                btnToggle.style.background = '#16233F';
                btnToggle.style.borderColor = '#16233F';
            });
        }
    })();
</script>
@endpush
