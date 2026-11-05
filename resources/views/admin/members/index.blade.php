@extends('layouts.app')

@section('header_title', 'Data Anggota')
@section('title', 'Data Anggota — Bank Mini')
@section('page-title', 'Data Anggota (Siswa Baru)')

@section('content')
<div class="card-glass p-4 mb-4">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="m-0 fw-bold text-dark">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Data Anggota (Siswa Baru)
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Daftar siswa baru belum aktif (hasil impor Excel) yang menunggu aktivasi rekening &amp; pembuatan PIN sebelum setoran awal di Teller.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">
                <i class="bi bi-hourglass-split me-1"></i> {{ $members->count() }} Siswa Belum Aktif
            </span>
            <a href="{{ route('admin.members.import.form') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #0F766E, #14B8A6); border: none; border-radius: 12px; padding: 9px 18px; font-weight: 500; font-size: 0.86rem; box-shadow: 0 8px 18px rgba(15, 118, 110, 0.2);">
                <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> Unggah Data Siswa (Excel)
            </a>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="mb-4">
        <form action="{{ route('admin.members.index') }}" method="GET" class="row g-2 align-items-center">
            {{-- Class Dropdown Filter --}}
            <div class="col-md-3 col-sm-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-funnel text-muted"></i>
                    </span>
                    <select name="class" class="form-select border-start-0" style="border-radius: 0 12px 12px 0;" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $classItem)
                            <option value="{{ $classItem }}" {{ request('class') == $classItem ? 'selected' : '' }}>
                                {{ $classItem }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Search Input with Physical Button --}}
            <div class="col-md-5 col-sm-6">
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Cari nama atau NIS siswa..." 
                           value="{{ request('search') }}"
                           style="border-radius: 12px 0 0 12px;">
                    <button type="submit" class="btn btn-primary px-3" style="background: linear-gradient(135deg, #16233F, #0E1830); border: none; border-radius: 0 12px 12px 0;" title="Cari Data">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            {{-- Reset Filter Button --}}
            @if(request('search') || request('class'))
            <div class="col-auto">
                <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filter">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            </div>
            @endif
        </form>
    </div>

    {{-- Data Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 130px;">NIS</th>
                    <th>Nama Siswa</th>
                    <th style="width: 140px;">Kelas</th>
                    <th style="width: 160px;">Username Sistem</th>
                    <th style="width: 150px;">Status Rekening</th>
                    <th class="text-center" style="width: 230px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($members as $member)
                <tr>
                    <td>
                        <span class="fw-bold text-dark font-monospace">{{ $member->student_id }}</span>
                    </td>
                    <td>
                        <span class="fw-semibold text-dark">{{ $member->student_name ?? $member->name }}</span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border px-2 py-1 rounded-pill">
                            {{ $member->grade_class ?: ($member->graduation_year ? 'Angkatan ' . $member->graduation_year : '-') }}
                        </span>
                    </td>
                    <td>
                        <code>{{ $member->user->username ?? ('siswa_' . $member->student_id) }}</code>
                    </td>
                    <td>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill">
                            <i class="bi bi-hourglass-split me-1"></i> Belum Aktif
                        </span>
                    </td>
                    <td class="text-center">
                        <div class="d-inline-flex gap-1 align-items-center">
                            {{-- Button Trigger Activation Modal --}}
                            <button type="button" 
                                    class="btn btn-sm btn-success rounded-pill px-3 fw-semibold shadow-sm" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalActivate{{ $member->id }}">
                                <i class="bi bi-shield-check me-1"></i> Aktifkan
                            </button>

                            {{-- Button Edit --}}
                            <a href="{{ route('admin.members.edit', $member->id) }}" 
                               class="btn btn-sm btn-outline-primary rounded-pill px-2" 
                               title="Edit Data Siswa">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            {{-- Button Delete --}}
                            <form action="{{ route('admin.members.destroy', $member->id) }}" 
                                  method="POST" 
                                  class="d-inline" 
                                  onsubmit="return confirm('Yakin hapus data calon anggota ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Hapus Data">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-folder2-open display-6 text-muted mb-2 d-block"></i>
                        <span class="fw-semibold">Tidak ada data anggota siswa baru (status belum aktif).</span>
                        <p class="small text-muted mb-0 mt-1">Semua siswa baru telah diaktifkan atau tidak cocok dengan filter pencarian.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- ======================================================== --}}
{{-- CRITICAL MODAL BUG FIX:                                    --}}
{{-- Modals are placed OUTSIDE .card-glass at the bottom of the --}}
{{-- view to completely eliminate z-index/backdrop freeze bugs  --}}
{{-- ======================================================== --}}
@foreach($members as $member)
<div class="modal fade" 
     id="modalActivate{{ $member->id }}" 
     tabindex="-1" 
     aria-labelledby="modalActivateLabel{{ $member->id }}" 
     aria-hidden="true" 
     style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-success-subtle p-2 text-success d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <i class="bi bi-shield-lock-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="modalActivateLabel{{ $member->id }}">
                            Aktivasi Rekening Siswa
                        </h5>
                        <p class="text-muted small mb-0">{{ $member->student_name ?? $member->name }} &bull; NIS: {{ $member->student_id }}</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Batal"></button>
            </div>
            
            <form action="{{ route('admin.members.activate', $member->id) }}" method="POST">
                @csrf
                <div class="modal-body py-4 px-4">
                    <div class="alert alert-success-subtle border border-success-subtle rounded-3 mb-3 p-3 text-dark" style="background-color: #ECFDF5; font-size: 0.88rem; line-height: 1.5;">
                        <i class="bi bi-info-circle-fill text-success fs-5 me-1 align-middle"></i>
                        Rekening akan diaktifkan secara otomatis. PIN Transaksi dan Password akan diatur ke default: <strong class="text-success">123456</strong>. Sistem akan membuatkan Nomor Rekening untuk siswa ini. Lanjutkan?
                    </div>

                    <div class="p-3 bg-light rounded-3">
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Nama Siswa:</span>
                            <span class="fw-semibold text-dark">{{ $member->student_name ?? $member->name }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">NIS:</span>
                            <span class="fw-semibold text-dark font-monospace">{{ $member->student_id }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">Kelas:</span>
                            <span class="fw-semibold text-dark">{{ $member->grade_class ?: ($member->graduation_year ? 'Angkatan ' . $member->graduation_year : '-') }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 small">
                            <span class="text-muted">PIN & Password Default:</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace">123456</span>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check-circle-fill me-1"></i> Ya, Aktifkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script>
    // Safeguard backdrop dismissal for Bootstrap 5
    document.addEventListener('DOMContentLoaded', function () {
        const activationModals = document.querySelectorAll('.modal');
        activationModals.forEach(function (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
                // Ensure backdrop and body scroll lock are completely cleared
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(b => b.remove());
                document.body.classList.remove('modal-open');
                document.body.style.removeProperty('padding-right');
                document.body.style.removeProperty('overflow');
            });
        });
    });
</script>
@endsection
