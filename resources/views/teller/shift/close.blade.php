@extends('layouts.app')

@section('title', 'Tutup Laci Kas (End of Day) — Bank Mini')
@section('header_title', 'Tutup Laci Kas')
@section('page-title', 'Tutup Laci Kas')
@section('page-subtitle', 'Rekapitulasi dan penutupan shift kasir')

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-12 col-md-8 col-lg-7 col-xl-6">

        {{-- Session Alerts --}}
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-4" role="alert">
                <i class="bi bi-x-circle-fill fs-4 text-danger flex-shrink-0"></i>
                <div class="small fw-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('warning'))
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-4" style="background-color: #FEF3C7; color: #92400E;" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-warning flex-shrink-0"></i>
                <div class="small fw-medium">{{ session('warning') }}</div>
            </div>
        @endif

        {{-- Card Penutupan Kasir --}}
        <div class="card bg-white border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-body p-4 p-sm-5 text-center">

                {{-- Icon Badge & Header --}}
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 68px; height: 68px; background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.15)); color: #DC2626;">
                    <i class="bi bi-safe2 fs-2"></i>
                </div>

                <h4 class="fw-bold text-dark mb-2">Tutup Laci Kas (End of Day)</h4>
                <p class="text-muted small mb-4 px-sm-2">
                    Silakan hitung dan masukkan total uang fisik yang ada di laci Anda saat ini untuk pencocokan saldo sistem.
                </p>

                {{-- Kalkulasi Otomatis Sistem --}}
                <div class="p-4 mb-4 rounded-4 text-start" style="background: rgba(244, 247, 246, 0.7); border: 1px solid rgba(22, 35, 63, 0.08);">
                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cpu-fill text-primary me-2"></i>Kalkulasi Sistem Otomatis (Real-Time)</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded-3 border">
                                <span class="text-muted small d-block">Saldo Awal (Modal)</span>
                                <span class="fs-6 fw-bold text-dark">Rp {{ number_format($saldoAwal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded-3 border">
                                <span class="text-muted small d-block">Total Uang Masuk</span>
                                <span class="fs-6 fw-bold text-success">+ Rp {{ number_format($totalSetoran, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded-3 border">
                                <span class="text-muted small d-block">Total Uang Keluar</span>
                                <span class="fs-6 fw-bold text-danger">- Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <div>
                            <span class="fw-bold text-dark fs-6">Target Saldo Akhir Komputer:</span>
                            <small class="text-muted d-block">Total uang tunai fisik yang seharusnya ada di laci kasir.</small>
                        </div>
                        <div class="text-end">
                            <span class="fs-4 fw-bold text-primary" id="systemBalance" data-val="{{ $targetSaldo }}">
                                Rp {{ number_format($targetSaldo, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Rincian Petugas Shift Bersama --}}
                @if(isset($breakdown) && count($breakdown) > 0)
                <div class="p-3 mb-4 rounded-4 text-start bg-light border">
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.9rem;">
                        <i class="bi bi-people-fill text-primary me-2"></i>Rincian Kontribusi Petugas (Shift Bersama)
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-borderless mb-0" style="font-size: 0.82rem;">
                            <thead>
                                <tr class="text-muted border-bottom">
                                    <th>Petugas</th>
                                    <th class="text-end">Setoran</th>
                                    <th class="text-end">Penarikan</th>
                                    <th class="text-end">Arus Kas Bersih</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($breakdown as $b)
                                <tr>
                                    <td class="fw-semibold text-dark">
                                        {{ $b['name'] }}
                                        @if($b['is_opener'])
                                            <span class="badge bg-primary-subtle text-primary py-0 px-1" style="font-size: 0.68rem;">Pembuka Laci</span>
                                        @endif
                                    </td>
                                    <td class="text-end text-success">+ Rp {{ number_format($b['deposit_total'], 0, ',', '.') }} <small class="text-muted">({{ $b['deposit_count'] }})</small></td>
                                    <td class="text-end text-danger">- Rp {{ number_format($b['withdrawal_total'], 0, ',', '.') }} <small class="text-muted">({{ $b['withdrawal_count'] }})</small></td>
                                    <td class="text-end fw-bold {{ $b['net_cash_flow'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                        Rp {{ number_format($b['net_cash_flow'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif

                {{-- Form End Shift --}}
                <form method="POST" action="{{ route('teller.shift.end') }}" class="text-start">
                    @csrf

                    <div class="mb-3">
                        <label for="physical_cash" class="form-label fw-semibold text-dark small">
                            Total Kas Fisik Akhir (Blind Count) <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold border-end-0 bg-light text-secondary px-3" style="border-radius: 12px 0 0 12px; border-color: #E5E7EB; font-size: 1rem;">
                                Rp
                            </span>
                            <input type="number" 
                                   id="physical_cash" 
                                   name="physical_cash" 
                                   value="{{ old('physical_cash', old('closing_balance')) }}" 
                                   min="0" 
                                   step="1000" 
                                   class="form-control border-start-0 ps-2 fs-5 fw-bold @error('physical_cash') is-invalid @enderror" 
                                   placeholder="0" 
                                   style="border-radius: 0 12px 12px 0; border-color: #E5E7EB; color: #16233F;" 
                                   required 
                                   autofocus>
                        </div>

                        @error('physical_cash')
                            <div class="invalid-feedback d-block mt-1 small">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text text-muted small mt-2 d-flex align-items-center gap-1">
                            <i class="bi bi-shield-lock-fill text-warning flex-shrink-0"></i>
                            <span>Prosedur <em>Blind Count</em>: Hitung uang riil tanpa memperkirakan selisih sistem.</span>
                        </div>
                    </div>

                    <!-- Live Status Rekonsiliasi -->
                    <div id="diffBadge" class="p-3 rounded-3 mb-3 d-none"></div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold text-dark small" for="notes">Catatan / Keterangan (Wajib jika ada selisih kas)</label>
                        <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Tuliskan keterangan bila terdapat selisih kas atau catatan transaksi...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback d-block mt-1 small">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-3 fw-semibold shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #111827, #1F2937); border: none;">
                        <i class="bi bi-lock-fill me-1"></i> Tutup Shift & Rekap Saldo
                    </button>
                </form>

                {{-- Link Kembali ke Dashboard --}}
                <div class="text-center mt-3 pt-2">
                    <a href="{{ route('teller.dashboard') }}" class="text-decoration-none small text-muted d-inline-flex align-items-center gap-1">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const physicalInput = document.getElementById('physical_cash');
    const systemBalanceEl = document.getElementById('systemBalance');
    const diffBadge = document.getElementById('diffBadge');

    if (physicalInput && systemBalanceEl && diffBadge) {
        const systemVal = parseInt(systemBalanceEl.getAttribute('data-val'));

        physicalInput.addEventListener('input', function() {
            const val = parseInt(this.value);
            if (isNaN(val)) {
                diffBadge.classList.add('d-none');
                return;
            }

            diffBadge.classList.remove('d-none');
            const diff = val - systemVal;

            if (diff === 0) {
                diffBadge.className = 'p-3 rounded-3 mb-3 bg-success-subtle border border-success-subtle text-success small';
                diffBadge.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i><strong>SEIMBANG (MATCH):</strong> Jumlah uang fisik di laci sama persis dengan saldo komputer.';
            } else if (diff > 0) {
                diffBadge.className = 'p-3 rounded-3 mb-3 bg-warning-subtle border border-warning-subtle text-warning-emphasis small';
                diffBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i><strong>SELISIH LEBIH:</strong> Fisik laci lebih banyak Rp ' + diff.toLocaleString('id-ID') + ' dari sistem komputer. Mohon isi kolom catatan.';
            } else {
                diffBadge.className = 'p-3 rounded-3 mb-3 bg-danger-subtle border border-danger-subtle text-danger small';
                diffBadge.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i><strong>SELISIH KURANG:</strong> Fisik laci kurang Rp ' + Math.abs(diff).toLocaleString('id-ID') + ' dari sistem komputer. Mohon isi kolom catatan.';
            }
        });
    }
</script>
@endpush
