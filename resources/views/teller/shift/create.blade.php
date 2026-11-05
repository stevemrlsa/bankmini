@extends('layouts.app')

@section('title', 'Buka Laci Kas (Modal Awal) — Bank Mini')
@section('header_title', 'Buka Laci Kas')
@section('page-title', 'Buka Laci Kas')
@section('page-subtitle', 'Pencatatan modal awal uang fisik laci teller')

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
        <div class="card bg-white border-0 shadow-sm" style="border-radius: 20px;">
            <div class="card-body p-4 p-sm-5 text-center">

                {{-- Icon Badge & Header --}}
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width: 68px; height: 68px; background: linear-gradient(135deg, rgba(22, 35, 63, 0.08), rgba(59, 91, 165, 0.12)); color: #16233F;">
                    <i class="bi bi-cash-stack fs-2"></i>
                </div>

                <h4 class="fw-bold text-dark mb-2">Buka Laci Kas (Modal Awal)</h4>
                <p class="text-muted small mb-0 px-sm-2">
                    Silakan masukkan nominal uang fisik yang ada di laci Anda saat ini sebelum melayani nasabah.
                </p>

                {{-- Form Store Shift --}}
                <form method="POST" action="{{ route('teller.shift.store') }}" class="mt-4 pt-2 text-start">
                    @csrf

                    <div class="mb-4">
                        <label for="opening_balance" class="form-label fw-semibold text-dark small">Nominal Kas Fisik Awal</label>
                        <div class="input-group">
                            <span class="input-group-text fw-bold border-end-0 bg-light text-secondary px-3" style="border-radius: 12px 0 0 12px; border-color: #E5E7EB; font-size: 1rem;">
                                Rp
                            </span>
                            <input type="number" 
                                   id="opening_balance" 
                                   name="opening_balance" 
                                   value="{{ old('opening_balance', 0) }}" 
                                   min="0" 
                                   step="1000" 
                                   class="form-control border-start-0 ps-2 fs-5 fw-bold @error('opening_balance') is-invalid @enderror" 
                                   placeholder="0" 
                                   style="border-radius: 0 12px 12px 0; border-color: #E5E7EB; color: #16233F;" 
                                   required 
                                   autofocus>
                        </div>
                        
                        @error('opening_balance')
                            <div class="invalid-feedback d-block mt-1 small">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="form-text text-muted small mt-2 d-flex align-items-center gap-1">
                            <i class="bi bi-info-circle text-primary flex-shrink-0"></i>
                            <span>Jika laci kas kosong saat mulai bertugas, masukkan nominal <strong>0</strong>.</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-3 fw-semibold shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #16233F, #0E1830); border: none;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Buka Laci & Mulai Shift
                    </button>
                </form>

            </div>
        </div>

    </div>
</div>
@endsection
