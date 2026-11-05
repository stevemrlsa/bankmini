@extends('layouts.app')

@section('title', 'Aktivasi Nasabah Baru — Bank Mini')
@section('header_title', 'Aktivasi Nasabah')
@section('page-title', 'Aktivasi Nasabah Baru')
@section('page-subtitle', 'One-Stop Service Aktivasi Rekening & Setoran Awal')

@push('styles')
<style>
    .card-glass {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255, 255, 255, 0.65);
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
    }
    .badge-soft-warning {
        background-color: #FEF3C7;
        color: #92400E;
        border: 1px solid #FDE68A;
    }
    .btn-action-activate {
        background: linear-gradient(135deg, #0F766E, #14B8A6);
        color: #fff;
        border: none;
        border-radius: 50px;
        font-weight: 600;
        padding: 6px 16px;
        box-shadow: 0 4px 12px rgba(15, 118, 110, 0.25);
        transition: all 0.2s ease;
    }
    .btn-action-activate:hover {
        background: linear-gradient(135deg, #0D655E, #0F766E);
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(15, 118, 110, 0.35);
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-3 py-2">

    {{-- Session Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert" style="background: #ECFDF5; color: #065F46;">
            <i class="bi bi-check-circle-fill fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 d-flex align-items-center gap-2 mb-4" role="alert" style="background: #FEF2F2; color: #991B1B;">
            <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0 mb-4" role="alert" style="background: #FEF2F2; color: #991B1B;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-shield-exclamation fs-5 text-danger"></i>
                <strong>Terdapat kesalahan pada input aktivasi:</strong>
            </div>
            <ul class="mb-0 mt-2 small ps-4">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Main Container Card --}}
    <div class="card-glass p-4 mb-4">
        {{-- Header Section --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
            <div>
                <h4 class="m-0 fw-bold text-dark">
                    <i class="bi bi-person-check-fill text-primary me-2"></i>One-Stop Aktivasi Rekening Siswa
                </h4>
                <p class="text-muted small mb-0 mt-1">
                    Lakukan pendaftaran akun, pembuatan nomor rekening, dan penerimaan setoran awal (minimal Rp 10.000) sekaligus.
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="badge badge-soft-warning px-3 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-hourglass-split me-1"></i> {{ $members->count() }} Menunggu Aktivasi
                </span>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <div class="mb-4">
            <form action="{{ route('teller.activations.index') }}" method="GET" class="row g-2 align-items-center">
                {{-- Class Filter --}}
                <div class="col-md-3 col-sm-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                            <i class="bi bi-funnel text-muted"></i>
                        </span>
                        <select name="class" class="form-select border-start-0" style="border-radius: 0 12px 12px 0;" onchange="this.form.submit()">
                            <option value="">Semua Kelas</option>
                            @foreach($classes as $c)
                                <option value="{{ $c }}" {{ request('class') == $c ? 'selected' : '' }}>
                                    {{ $c }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Search Bar --}}
                <div class="col-md-5 col-sm-6">
                    <div class="input-group">
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Cari nama atau NIS siswa..." 
                               value="{{ request('search') }}"
                               style="border-radius: 12px 0 0 12px;">
                        <button type="submit" class="btn btn-primary px-3" style="background: linear-gradient(135deg, #0F766E, #14B8A6); border: none; border-radius: 0 12px 12px 0;" title="Cari Data">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>

                {{-- Reset Button --}}
                @if(request('search') || request('class'))
                    <div class="col-auto">
                        <a href="{{ route('teller.activations.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filter">
                            <i class="bi bi-x-circle me-1"></i> Reset
                        </a>
                    </div>
                @endif
            </form>
        </div>

        {{-- Inactive Students Table --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 140px;">NIS</th>
                        <th>Nama Siswa</th>
                        <th style="width: 150px;">Kelas</th>
                        <th style="width: 150px;">Status</th>
                        <th class="text-center" style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $item)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark font-monospace">{{ $item->student_id }}</span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $item->student_name ?? $item->name }}</div>
                                <small class="text-muted">Username usulan: <code>siswa_{{ $item->student_id }}</code></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                                    {{ $item->grade_class ?: ($item->graduation_year ? 'Angkatan ' . $item->graduation_year : '-') }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-soft-warning px-2 py-1 rounded-pill">
                                    <i class="bi bi-hourglass-split me-1"></i> Belum Aktif
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button" 
                                        class="btn btn-action-activate" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalActivate{{ $item->id }}">
                                    <i class="bi bi-shield-check me-1"></i> Aktivasi
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="bi bi-folder-check display-6 text-muted mb-2 d-block"></i>
                                <span class="fw-semibold">Tidak ada siswa yang menunggu aktivasi.</span>
                                <p class="small text-muted mb-0 mt-1">Semua data siswa baru telah berhasil diaktivasi atau tidak cocok dengan filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- MODALS SECTION: Placed OUTSIDE the table container card   --}}
{{-- to prevent z-index/backdrop freeze issues                --}}
{{-- ======================================================== --}}
@foreach($members as $item)
    <div class="modal fade" 
         id="modalActivate{{ $item->id }}" 
         tabindex="-1" 
         aria-labelledby="modalActivateLabel{{ $item->id }}" 
         aria-hidden="true" 
         style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
                {{-- Modal Header --}}
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #E6FFFA; color: #0F766E;">
                            <i class="bi bi-person-plus-fill fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark m-0" id="modalActivateLabel{{ $item->id }}">
                                One-Stop Aktivasi Rekening Siswa
                            </h5>
                            <p class="text-muted small mb-0">
                                {{ $item->student_name ?? $item->name }} &bull; NIS: <strong>{{ $item->student_id }}</strong>
                            </p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
                </div>

                {{-- Modal Form --}}
                <form action="{{ route('teller.activations.activate', $item->id) }}" method="POST" id="formActivate{{ $item->id }}">
                    @csrf
                    <input type="hidden" name="active_modal_id" value="{{ $item->id }}">

                    <div class="modal-body py-4 px-4">
                        {{-- Information Box --}}
                        <div class="alert alert-info border-0 rounded-3 mb-4 p-3" style="background-color: #F0FDF4; border-left: 4px solid #10B981 !important; color: #166534;">
                            <div class="d-flex gap-2">
                                <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
                                <div class="small">
                                    <strong>Instruksi Teller:</strong> Lengkapi username, kontak siswa, dan nominal <strong>Setoran Awal (Minimal Rp 10.000)</strong>. Sistem otomatis mencatat mutasi kas, jurnal akuntansi, dan mengaktifkan buku tabungan. Password dan PIN default diatur ke <code>123456</code>.
                                </div>
                            </div>
                        </div>

                        {{-- Student Summary Box --}}
                        <div class="p-3 bg-light rounded-3 mb-4 border">
                            <div class="row g-2 small">
                                <div class="col-sm-6">
                                    <span class="text-muted">Nama Siswa:</span>
                                    <div class="fw-bold text-dark">{{ $item->student_name ?? $item->name }}</div>
                                </div>
                                <div class="col-sm-3">
                                    <span class="text-muted">NIS:</span>
                                    <div class="fw-bold font-monospace text-dark">{{ $item->student_id }}</div>
                                </div>
                                <div class="col-sm-3">
                                    <span class="text-muted">Kelas:</span>
                                    <div class="fw-bold text-dark">{{ $item->grade_class ?: ($item->graduation_year ? 'Angkatan ' . $item->graduation_year : '-') }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Form Inputs Grid --}}
                        <div class="row g-3">
                            {{-- Username --}}
                            <div class="col-md-6">
                                <label for="username_{{ $item->id }}" class="form-label fw-semibold text-dark small">
                                    Username Akun Siswa <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-at"></i></span>
                                    <input type="text" 
                                           id="username_{{ $item->id }}" 
                                           name="username" 
                                           class="form-control @error('username') is-invalid @enderror" 
                                           value="{{ old('active_modal_id') == $item->id ? old('username') : ('siswa_' . $item->student_id) }}" 
                                           required>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Digunakan untuk login siswa ke Portal Nasabah.</small>
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6">
                                <label for="email_{{ $item->id }}" class="form-label fw-semibold text-dark small">
                                    Alamat Email Siswa <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-envelope"></i></span>
                                    <input type="email" 
                                           id="email_{{ $item->id }}" 
                                           name="email" 
                                           class="form-control @error('email') is-invalid @enderror" 
                                           placeholder="nama@bankmini.sch.id"
                                           value="{{ old('active_modal_id') == $item->id ? old('email') : ($item->user->email ?? ('siswa_' . $item->student_id . '@bankmini.sch.id')) }}" 
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Untuk konfirmasi transaksi dan reset password.</small>
                            </div>

                            {{-- WhatsApp Number --}}
                            <div class="col-md-6">
                                <label for="wa_{{ $item->id }}" class="form-label fw-semibold text-dark small">
                                    Nomor WhatsApp (Aktif) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="bi bi-whatsapp text-success"></i></span>
                                    <input type="text" 
                                           id="wa_{{ $item->id }}" 
                                           name="whatsapp_number" 
                                           class="form-control @error('whatsapp_number') is-invalid @enderror" 
                                           placeholder="081234567890" 
                                           value="{{ old('active_modal_id') == $item->id ? old('whatsapp_number') : ($item->user->whatsapp_number ?? '') }}" 
                                           required>
                                    @error('whatsapp_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Nomor kontak wali murid/siswa untuk notifikasi saldo.</small>
                            </div>

                            {{-- Initial Deposit Amount --}}
                            <div class="col-md-6">
                                <label for="amount_{{ $item->id }}" class="form-label fw-semibold text-dark small">
                                    Nominal Setoran Awal (Rp) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-dark fw-bold">Rp</span>
                                    <input type="number" 
                                           id="amount_{{ $item->id }}" 
                                           name="amount" 
                                           min="10000" 
                                           step="1000" 
                                           class="form-control fw-bold text-success fs-5 @error('amount') is-invalid @enderror" 
                                           value="{{ old('active_modal_id') == $item->id ? old('amount') : '10000' }}" 
                                           required>
                                    @error('amount')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">
                                    <i class="bi bi-shield-check text-success me-1"></i><strong>Minimal Rp 10.000</strong> sebagai saldo awal tabungan.
                                </small>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="modal-footer border-0 pt-0 pb-4 px-4 gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">
                            Batal
                        </button>
                        <button type="submit" class="btn btn-action-activate px-4">
                            <i class="bi bi-check-circle-fill me-1"></i> Proses Aktivasi &amp; Setor
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

@push('scripts')
@if(old('active_modal_id'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modalId = 'modalActivate{{ old('active_modal_id') }}';
        var modalEl = document.getElementById(modalId);
        if (modalEl) {
            var myModal = new bootstrap.Modal(modalEl);
            myModal.show();
        }
    });
</script>
@endif
@endpush

@endsection
