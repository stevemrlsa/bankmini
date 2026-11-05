@extends('layouts.app')

@section('title', 'Penutupan Kas Harian (Cash Closing)')
@section('header_title', 'Penutupan Kas')
@section('page-title', 'Penutupan Kas Harian')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">

        {{-- Alerts --}}
        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-4" role="alert">
                <i class="bi bi-x-circle-fill fs-4 text-danger flex-shrink-0"></i>
                <div class="small fw-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-4" role="alert">
                <i class="bi bi-check-circle-fill fs-4 text-success flex-shrink-0"></i>
                <div class="small fw-medium">{{ session('success') }}</div>
            </div>
        @endif

        <div class="card-glass">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div style="width: 50px; height: 50px; border-radius: 16px; background: linear-gradient(135deg, #16233F, #3B5BA5); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                    <i class="bi bi-journal-check"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-1 text-dark">Laporan Penutupan Kas Harian Teller</h5>
                    <p class="text-muted small mb-0">Rekonsiliasi otomatis kas loket hari ini: {{ now()->translatedFormat('l, d F Y') }}</p>
                </div>
            </div>

            <!-- Kalkulasi Otomatis Sistem -->
            <div class="p-4 mb-4 rounded-4" style="background: rgba(244, 247, 246, 0.7); border: 1px solid rgba(22, 35, 63, 0.08);">
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
                            <span class="text-muted small d-block">Total Uang Masuk (Setoran)</span>
                            <span class="fs-6 fw-bold text-success">+ Rp {{ number_format($totalSetoran, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-white rounded-3 border">
                            <span class="text-muted small d-block">Total Uang Keluar (Penarikan)</span>
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

            <!-- Form Fisik -->
            <form action="{{ route('teller.report.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-bold text-dark" for="physical_cash">
                        Hitung & Masukkan Uang Fisik di Laci Kasir (Rp) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white fw-bold">Rp</span>
                        <input
                            type="number"
                            name="physical_cash"
                            id="physical_cash"
                            class="form-control form-control-lg fw-bold text-dark @error('physical_cash') is-invalid @enderror"
                            placeholder="Contoh: 150000"
                            value="{{ old('physical_cash') }}"
                            required
                            autofocus
                        >
                    </div>
                    @error('physical_cash')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                    <small class="text-muted mt-1 d-block">Ketik jumlah lembaran dan koin fisik yang telah Anda hitung di laci.</small>
                </div>

                <!-- Live Status Rekonsiliasi -->
                <div id="diffBadge" class="p-3 rounded-3 mb-3 d-none"></div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-dark" for="notes">Catatan / Keterangan (Opsional jika seimbang, wajib jika ada selisih)</label>
                    <textarea name="notes" id="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Tuliskan keterangan bila terdapat selisih kas atau catatan transaksi...">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback d-block mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-pill" style="background: linear-gradient(135deg, #16233F, #0E1830); border: none; font-size: 1rem; box-shadow: 0 10px 24px rgba(22, 35, 63, 0.2);">
                    <i class="bi bi-send-check me-2"></i> Kirim Laporan Penutupan ke Administrator
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const physicalInput = document.getElementById('physical_cash');
    const systemVal = parseInt(document.getElementById('systemBalance').getAttribute('data-val'));
    const diffBadge = document.getElementById('diffBadge');

    physicalInput.addEventListener('input', function() {
        const val = parseInt(this.value);
        if (isNaN(val)) {
            diffBadge.classList.add('d-none');
            return;
        }

        diffBadge.classList.remove('d-none');
        const diff = val - systemVal;

        if (diff === 0) {
            diffBadge.className = 'p-3 rounded-3 mb-3 bg-success-subtle border border-success-subtle text-success';
            diffBadge.innerHTML = '<i class="bi bi-check-circle-fill me-2"></i><strong>SEIMBANG (MATCH):</strong> Jumlah uang fisik di laci sama persis dengan saldo komputer.';
        } else if (diff > 0) {
            diffBadge.className = 'p-3 rounded-3 mb-3 bg-warning-subtle border border-warning-subtle text-warning-emphasis';
            diffBadge.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-2"></i><strong>SELISIH LEBIH:</strong> Fisik laci lebih banyak Rp ' + diff.toLocaleString('id-ID') + ' dari sistem komputer. Mohon isi kolom catatan.';
        } else {
            diffBadge.className = 'p-3 rounded-3 mb-3 bg-danger-subtle border border-danger-subtle text-danger';
            diffBadge.innerHTML = '<i class="bi bi-x-circle-fill me-2"></i><strong>SELISIH KURANG:</strong> Fisik laci kurang Rp ' + Math.abs(diff).toLocaleString('id-ID') + ' dari sistem komputer. Mohon isi kolom catatan.';
        }
    });
</script>
@endpush

