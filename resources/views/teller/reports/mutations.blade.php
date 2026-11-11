@extends('layouts.app')

@section('title', 'Laporan Mutasi Nasabah — Teller')
@section('header_title', 'Laporan Mutasi Nasabah')
@section('page-title', 'Laporan Mutasi Nasabah Teller')
@section('page-subtitle', 'Rekap dan cetak PDF mutasi transaksi nasabah yang Anda layani saat bertugas')

@section('content')
@push('styles')
<style>
    .rek-number {
        font-weight: 600;
        color: #16233F;
        letter-spacing: 0.3px;
    }
    .btn.btn-sm {
        padding: 6px 14px;
        font-size: 0.8rem;
    }
</style>
@endpush
@php
    $activePeriod = $periodData['period'];
    $baseFilterParams = [
        'type'       => $normalizedType,
        'scope'      => $scope,
        'search'     => $search,
    ];
    $pdfParams = array_merge($baseFilterParams, [
        'period'     => $activePeriod,
        'start_date' => $periodData['start_date'],
        'end_date'   => $periodData['end_date'],
    ]);
@endphp

<div class="card-glass mb-4">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div>
            <h5 class="fw-bold mb-1 text-dark">
                <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>Laporan Mutasi Transaksi Nasabah
            </h5>
            <small class="text-muted">
                Petugas: <strong class="text-dark">{{ $teller->officer_name ?: $teller->username }}</strong>
                <span class="mx-1">·</span>
                Periode Aktif: <strong class="text-primary">{{ $periodData['label'] }}</strong>
            </small>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <a href="{{ route('teller.reports.mutations.pdf', $pdfParams) }}"
               target="_blank"
               class="btn btn-danger rounded-pill px-3 fw-semibold shadow-sm">
                <i class="bi bi-printer-fill me-1"></i> Preview / Cetak PDF
            </a>
            <a href="{{ route('teller.reports.mutations.pdf', array_merge($pdfParams, ['download' => 1])) }}"
               class="btn btn-outline-danger rounded-pill px-3 fw-semibold">
                <i class="bi bi-download me-1"></i> Unduh PDF
            </a>
        </div>
    </div>

    {{-- Tombol Preset Periode Cepat --}}
    <div class="d-flex align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
        <span class="small fw-semibold text-muted me-1">Periode Cepat:</span>

        <a href="{{ route('teller.reports.mutations', array_merge($baseFilterParams, ['period' => 'today'])) }}"
           class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'today' ? 'btn-dark' : 'btn-outline-secondary' }}"
           style="{{ $activePeriod === 'today' ? 'background: #16233F; border-color: #16233F;' : '' }}">
            <i class="bi bi-calendar-check me-1"></i> Hari Ini
        </a>

        <a href="{{ route('teller.reports.mutations', array_merge($baseFilterParams, ['period' => 'yesterday'])) }}"
           class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'yesterday' ? 'btn-dark' : 'btn-outline-secondary' }}"
           style="{{ $activePeriod === 'yesterday' ? 'background: #16233F; border-color: #16233F;' : '' }}">
            <i class="bi bi-calendar-minus me-1"></i> Kemarin
        </a>

        <a href="{{ route('teller.reports.mutations', array_merge($baseFilterParams, ['period' => 'this_month'])) }}"
           class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'this_month' ? 'btn-dark' : 'btn-outline-secondary' }}"
           style="{{ $activePeriod === 'this_month' ? 'background: #16233F; border-color: #16233F;' : '' }}">
            <i class="bi bi-calendar3 me-1"></i> Bulan Ini
        </a>

        <button type="button"
                id="btnToggleCustomPeriod"
                class="btn btn-sm rounded-pill px-3 fw-semibold {{ $activePeriod === 'custom' ? 'btn-dark' : 'btn-outline-secondary' }}"
                style="{{ $activePeriod === 'custom' ? 'background: #16233F; border-color: #16233F;' : '' }}">
            <i class="bi bi-calendar-range me-1"></i> Rentang Kustom
        </button>
    </div>

    {{-- Form Filter Lengkap --}}
    <form method="GET" action="{{ route('teller.reports.mutations') }}" id="tellerMutationFilterForm" class="row g-2 align-items-end mt-2">
        <input type="hidden" name="period" id="inputPeriodMode" value="{{ $activePeriod }}">

        <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-semibold text-muted mb-1">Cakupan Petugas</label>
            <select name="scope" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                <option value="mine" {{ $scope === 'mine' ? 'selected' : '' }}>Hanya Transaksi Saya</option>
                <option value="shift" {{ $scope === 'shift' ? 'selected' : '' }}>Semua Transaksi di Shift Saya</option>
            </select>
        </div>

        <div class="col-md-3 col-sm-6">
            <label class="form-label small fw-semibold text-muted mb-1">Jenis Mutasi</label>
            <select name="type" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                <option value="all" {{ $normalizedType === 'all' ? 'selected' : '' }}>Semua Mutasi</option>
                <option value="deposit" {{ $normalizedType === 'deposit' ? 'selected' : '' }}>Setoran Tunai (Deposit)</option>
                <option value="withdrawal" {{ $normalizedType === 'withdrawal' ? 'selected' : '' }}>Penarikan Tunai (Withdrawal)</option>
            </select>
        </div>

        <div class="col-md-3 col-sm-6 custom-date-field {{ $activePeriod === 'custom' ? '' : 'd-none' }}">
            <label class="form-label small fw-semibold text-muted mb-1">Dari Tanggal</label>
            <input type="date" name="start_date" class="form-control form-control-sm rounded-3" value="{{ $periodData['start_date'] }}">
        </div>

        <div class="col-md-3 col-sm-6 custom-date-field {{ $activePeriod === 'custom' ? '' : 'd-none' }}">
            <label class="form-label small fw-semibold text-muted mb-1">Sampai Tanggal</label>
            <input type="date" name="end_date" class="form-control form-control-sm rounded-3" value="{{ $periodData['end_date'] }}">
        </div>

        <div class="col-md-4 col-sm-8">
            <label class="form-label small fw-semibold text-muted mb-1">Cari Nasabah (Opsional)</label>
            <input type="text" name="search" class="form-control form-control-sm rounded-3"
                   placeholder="Nama siswa, NIS, atau No. Rekening..." value="{{ $search }}">
        </div>

        <div class="col-auto d-flex gap-2">
            <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-semibold shadow-sm"
                    style="background: linear-gradient(135deg, #16233F, #0E1830); border: none; padding-top: 6px; padding-bottom: 6px;">
                <i class="bi bi-funnel-fill me-1"></i> Terapkan
            </button>
            @if($activePeriod !== 'today' || $normalizedType !== 'all' || $scope !== 'mine' || !empty($search))
                <a href="{{ route('teller.reports.mutations') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                    Reset
                </a>
            @endif
        </div>
    </form>

    @if($scope === 'shift')
        <div class="alert alert-info border-0 rounded-3 mt-3 mb-0 py-2 px-3 small d-flex align-items-center gap-2"
             style="background: rgba(59, 130, 246, 0.1); color: #1E40AF;">
            <i class="bi bi-people-fill fs-6 flex-shrink-0"></i>
            <div>
                @if($participatingTellers->count() > 1)
                    <strong>Mode Shift Bersama Aktif:</strong> Menampilkan transaksi dari seluruh teller yang bertugas bersama Anda pada periode ini
                    ({{ $participatingTellers->map(fn ($u) => $u->officer_name ?: $u->username)->implode(', ') }}).
                @else
                    <strong>Mode Shift Bersama:</strong> Pada periode ini Anda bertugas secara mandiri (tidak ada rekan shift lain yang tercatat), sehingga hanya menampilkan transaksi Anda.
                @endif
            </div>
        </div>
    @endif
</div>

{{-- Ringkasan Statistik --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card-glass p-3 h-100 mb-0">
            <span class="text-muted small d-block mb-1">Total Mutasi Ditampilkan</span>
            <span class="fs-5 fw-bold text-dark">{{ number_format($summary['total_count'], 0, ',', '.') }} Transaksi</span>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card-glass p-3 h-100 mb-0">
            <span class="text-muted small d-block mb-1">Total Setoran (Cash-In)</span>
            <span class="fs-5 fw-bold text-success">+ Rp {{ number_format($summary['total_deposits'], 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card-glass p-3 h-100 mb-0">
            <span class="text-muted small d-block mb-1">Total Penarikan (Cash-Out)</span>
            <span class="fs-5 fw-bold text-danger">- Rp {{ number_format($summary['total_withdrawals'], 0, ',', '.') }}</span>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card-glass p-3 h-100 mb-0">
            <span class="text-muted small d-block mb-1">Selisih Bersih Kas Mutasi</span>
            <span class="fs-5 fw-bold {{ $summary['net_change'] >= 0 ? 'text-primary' : 'text-danger' }}">
                {{ $summary['net_change'] >= 0 ? '+ ' : '- ' }}Rp {{ number_format(abs($summary['net_change']), 0, ',', '.') }}
            </span>
        </div>
    </div>
</div>

{{-- Tabel Mutasi Nasabah --}}
<div class="card-glass">
    <div class="pb-3 mb-2 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="fw-bold text-dark mb-0">Daftar Mutasi Nasabah ({{ $periodData['label'] }})</h6>
        <span class="badge bg-light text-secondary border rounded-pill px-3 py-1">
            {{ $scope === 'shift' ? 'Cakupan: Shift Bersama' : 'Cakupan: Hanya Transaksi Saya' }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" style="font-size: 0.86rem;">
            <thead>
                <tr>
                    <th>Waktu Transaksi</th>
                    <th>Kode Trx</th>
                    <th>No. Rekening</th>
                    <th>Nama Nasabah</th>
                    <th>Kelas</th>
                    <th>Jenis Mutasi</th>
                    <th class="text-end">Nominal</th>
                    <th>Petugas Teller</th>
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
                        <td><span class="rek-number">{{ $tx->saver->account_number ?? '-' }}</span></td>
                        <td>
                            <span class="fw-semibold text-dark d-block">{{ $tx->saver->student_name ?? 'Nasabah Terhapus' }}</span>
                            @if($tx->saver?->student_id)
                                <small class="text-muted">NIS: {{ $tx->saver->student_id }}</small>
                            @endif
                        </td>
                        <td>{{ $tx->saver->grade_class ?? '-' }}</td>
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
                        <td>{{ $tx->user->officer_name ?? $tx->user->username ?? 'Teller' }}</td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">{{ $tx->status ?? 'Approved' }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            Belum ada mutasi transaksi nasabah pada periode dan filter yang dipilih.
                        </td>
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
        const btnToggle = document.getElementById('btnToggleCustomPeriod');
        const inputMode = document.getElementById('inputPeriodMode');
        const customFields = document.querySelectorAll('.custom-date-field');

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
