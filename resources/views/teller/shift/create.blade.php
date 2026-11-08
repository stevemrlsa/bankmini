@extends('layouts.app')

@section('title', 'Buka Laci Kas (Modal Awal) — Bank Mini')
@section('header_title', 'Buka Laci Kas')
@section('page-title', 'Buka Laci Kas')
@section('page-subtitle', 'Pencatatan modal awal uang fisik laci teller')

@push('styles')
<style>
    .nominal-chip {
        display: inline-flex;
        align-items: center;
        padding: 6px 14px;
        background: #F1F5F9;
        border: 1px solid #CBD5E1;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s ease;
        user-select: none;
    }
    .nominal-chip:hover {
        background: #E2E8F0;
        color: #0F172A;
        border-color: #94A3B8;
        transform: translateY(-1px);
    }
    .shift-create-card {
        background: #ffffff;
        border-radius: 24px;
        border: 1px solid rgba(226, 232, 240, 0.9);
        box-shadow: 0 20px 45px rgba(22, 35, 63, 0.08);
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center py-4">
    <div class="col-12 col-md-8 col-lg-6 col-xl-5">

        {{-- Session Alert Warning / Error dari Middleware --}}
        @if (session('warning'))
            <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4" style="border-radius: 16px; background-color: #FEF3C7; color: #92400E;" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-4 text-warning flex-shrink-0"></i>
                <div class="small fw-medium">{{ session('warning') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4" style="border-radius: 16px;" role="alert">
                <i class="bi bi-x-circle-fill fs-4 text-danger flex-shrink-0"></i>
                <div class="small fw-medium">{{ session('error') }}</div>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4" style="border-radius: 16px;" role="alert">
                <i class="bi bi-check-circle-fill fs-4 text-success flex-shrink-0"></i>
                <div class="small fw-medium">{{ session('success') }}</div>
            </div>
        @endif

        {{-- Card Form Buka Laci Kas --}}
        <div class="shift-create-card">
            {{-- Header Card --}}
            <div class="p-4 pb-2">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 52px; height: 52px; border-radius: 16px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(5, 150, 105, 0.15);">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark m-0">Buka Laci Kas (Modal Awal)</h5>
                        <p class="text-muted small mb-0 mt-1">Masukkan nominal uang tunai fisik yang ada di laci Anda saat ini sebelum melayani nasabah.</p>
                    </div>
                </div>
            </div>

            {{-- Form Store Shift --}}
            <form method="POST" action="{{ route('teller.shift.store') }}" id="formBukaLaci" class="p-4 pt-2">
                @csrf

                {{-- Information Box --}}
                <div class="p-3 rounded-3 mb-3 border" style="background: #F8FAFC; border-color: #E2E8F0 !important;">
                    <div class="d-flex align-items-center justify-content-between small mb-1">
                        <span class="text-muted">Status Shift:</span>
                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-pill fw-semibold">Belum Dibuka</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 small text-muted mt-2">
                        <i class="bi bi-shield-lock-fill text-success flex-shrink-0"></i>
                        <span>Hitung fisik uang tunai di laci sebelum memulai. <strong>Kas awal tidak boleh Rp 0</strong> agar penarikan nasabah tidak terhambat.</span>
                    </div>
                </div>

                {{-- Alert Error Client-Side --}}
                <div id="clientErrorAlert" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3" role="alert"></div>

                {{-- Input Nominal Kas Fisik Awal --}}
                <div class="mb-3">
                    <label for="opening_balance" class="form-label fw-semibold small text-dark">
                        Nominal Kas Fisik Awal (Rp) <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light fw-bold text-dark px-3" style="border-radius: 12px 0 0 12px;">Rp</span>
                        <input type="text" 
                               id="opening_balance" 
                               name="opening_balance" 
                               class="form-control rupiah-input fw-bold fs-4 text-success @error('opening_balance') is-invalid @enderror" 
                               placeholder="Contoh: 100.000" 
                               value="{{ old('opening_balance') }}" 
                               style="border-radius: 0 12px 12px 0;"
                               required 
                               autofocus 
                               autocomplete="off">
                    </div>

                    @error('opening_balance')
                        <div class="text-danger small mt-1 fw-medium">
                            <i class="bi bi-exclamation-circle me-1"></i> {{ $message }}
                        </div>
                    @enderror

                    {{-- Quick Nominal Chips --}}
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <span class="nominal-chip" onclick="setNominal('100.000')">+Rp 100.000</span>
                        <span class="nominal-chip" onclick="setNominal('200.000')">+Rp 200.000</span>
                        <span class="nominal-chip" onclick="setNominal('500.000')">+Rp 500.000</span>
                        <span class="nominal-chip" onclick="setNominal('1.000.000')">+Rp 1.000.000</span>
                        <span class="nominal-chip" onclick="setNominal('2.000.000')">+Rp 2.000.000</span>
                    </div>
                </div>

                {{-- Keterangan (Opsional) --}}
                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold small text-dark">Keterangan (Opsional)</label>
                    <input type="text" 
                           id="notes" 
                           name="notes" 
                           class="form-control rounded-3" 
                           placeholder="Contoh: Kas fisik awal shift pagi dari brankas" 
                           value="{{ old('notes') }}">
                </div>

                {{-- Action Buttons --}}
                <div class="d-flex gap-2">
                    <button type="submit" id="btnSubmitShift" class="btn btn-success w-100 py-3 fw-bold rounded-pill shadow-sm" style="background: linear-gradient(135deg, #059669, #10B981); border: none; font-size: 0.96rem;">
                        <i class="bi bi-check-circle-fill me-1"></i> Buka Laci & Mulai Shift
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const openingInput = document.getElementById('opening_balance');
    const formBukaLaci = document.getElementById('formBukaLaci');
    const clientErrorAlert = document.getElementById('clientErrorAlert');

    // Helper Fungsi Format Rupiah
    function formatRupiah(angka) {
        let number_string = angka.replace(/[^,\d]/g, '').toString();
        let split = number_string.split(',');
        let sisa = split[0].length % 3;
        let rupiah = split[0].substr(0, sisa);
        let ribuan = split[0].substr(sisa).match(/\d{3}/gi);

        if (ribuan) {
            let separator = sisa ? '.' : '';
            rupiah += separator + ribuan.join('.');
        }

        return split[1] !== undefined ? rupiah + ',' + split[1] : rupiah;
    }

    // Quick Chip Click Handler
    window.setNominal = function(val) {
        if (openingInput) {
            openingInput.value = val;
            openingInput.classList.remove('is-invalid');
            if (clientErrorAlert) clientErrorAlert.classList.add('d-none');
            openingInput.focus();
        }
    };

    if (openingInput) {
        openingInput.addEventListener('keyup', function(e) {
            this.value = formatRupiah(this.value);
            if (clientErrorAlert) clientErrorAlert.classList.add('d-none');
        });

        openingInput.addEventListener('change', function(e) {
            this.value = formatRupiah(this.value);
        });
    }

    // Client-Side Validation on Submit (Mencegah submit Rp 0 atau kosong)
    if (formBukaLaci) {
        formBukaLaci.addEventListener('submit', function(e) {
            const rawVal = openingInput ? openingInput.value.replace(/\./g, '').trim() : '';
            const numVal = parseInt(rawVal, 10);

            if (!rawVal || isNaN(numVal) || numVal < 1000) {
                e.preventDefault();
                if (clientErrorAlert) {
                    clientErrorAlert.textContent = 'Nominal kas fisik awal laci wajib diisi minimal Rp 1.000 dan tidak boleh kosong atau Rp 0. Silakan masukkan uang fisik nyata di laci Anda.';
                    clientErrorAlert.classList.remove('d-none');
                }
                if (openingInput) {
                    openingInput.classList.add('is-invalid');
                    openingInput.focus();
                }
                return false;
            }
        });
    }
</script>
@endpush
