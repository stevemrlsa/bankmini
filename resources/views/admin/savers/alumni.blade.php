@extends('layouts.app')

@section('header_title', 'Data Siswa Lulus (Alumni)')
@section('page-title', 'Data Siswa Lulus (Alumni)')
@section('page-subtitle', 'Arsip rekening nasabah yang telah lulus (Kelas XII) beserta saldo dan riwayat transaksi')

@section('content')
<div class="card-glass p-4 mb-4">
    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h4 class="m-0 fw-bold text-dark">
                <i class="bi bi-mortarboard-fill text-primary me-2"></i>Data Siswa Lulus (Alumni Kelas XII)
            </h4>
            <p class="text-muted small mb-0 mt-1">
                Data siswa kelas akhir yang telah lulus dipisahkan di halaman ini. Seluruh riwayat transaksi dan sisa saldo tetap tersimpan aman dan dapat diaktifkan kembali kapan saja.
            </p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold">
                <i class="bi bi-people-fill me-1"></i> Total {{ $totalAlumniCount ?? $alumni->count() }} Siswa Lulus
            </span>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-bold">
                <i class="bi bi-wallet2 me-1"></i> Saldo Mengendap Alumni: Rp {{ number_format($totalAlumniSaldo ?? 0, 0, ',', '.') }}
            </span>
        </div>
    </div>

    {{-- Filter & Search Form --}}
    <div class="mb-3">
        <form action="{{ route('admin.alumni.index') }}" method="GET" class="row g-2 align-items-center" onsubmit="const s = this.querySelector('input[name=search]'); const c = this.querySelector('select[name=class]'); if(s && s.value.trim() !== '' && c) { c.disabled = true; }">
            <div class="col-md-4 col-sm-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0" style="border-radius: 12px 0 0 12px;">
                        <i class="bi bi-mortarboard text-primary"></i>
                    </span>
                    <select name="class" class="form-select border-start-0 fw-semibold" style="border-radius: 0 12px 12px 0;" onchange="const s = this.form.querySelector('input[name=search]'); if(s) s.value=''; this.form.submit()">
                        @if(request('search'))
                            <option value="" selected>-- Semua Kelas Alumni (Pencarian Aktif) --</option>
                        @elseif($classes->isEmpty())
                            <option value="">Belum Ada Kelas Lulus</option>
                        @endif
                        @foreach($classes as $c)
                            <option value="{{ $c }}" {{ (!request('search') && ($currentClass ?? request('class')) == $c) ? 'selected' : '' }}>
                                {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-5 col-sm-6">
                <div class="input-group">
                    <input type="text" 
                           name="search" 
                           class="form-control" 
                           placeholder="Cari nama, NIS, atau No. Rekening alumni di semua kelas..." 
                           value="{{ request('search') }}"
                           style="border-radius: 12px 0 0 12px;">
                    <button type="submit" class="btn btn-primary px-3" style="background: linear-gradient(135deg, #16233F, #0E1830); border: none; border-radius: 0 12px 12px 0;" title="Cari Data">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>

            @if(request('search') || request('class'))
                <div class="col-auto">
                    <a href="{{ route('admin.alumni.index') }}" class="btn btn-outline-secondary rounded-pill px-3">
                        <i class="bi bi-x-circle me-1"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>

    {{-- Per-Class Navigation Header Bar --}}
    @if(!request('search') && ($totalClasses ?? 0) > 0 && !empty($currentClass))
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 mb-3 rounded-4 border" style="background: #F8FAFC;">
            <div>
                @if(!empty($prevClass))
                    <a href="{{ route('admin.alumni.index', ['class' => $prevClass]) }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold">
                        <i class="bi bi-arrow-left me-1"></i> Kelas Sebelumnya ({{ $prevClass }})
                    </a>
                @else
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>
                        <i class="bi bi-arrow-left me-1"></i> Kelas Sebelumnya
                    </button>
                @endif
            </div>

            <div class="text-center">
                <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                    Menampilkan Tabel Alumni: <span class="text-primary">{{ $currentClass }}</span>
                </span>
                <small class="text-muted d-block" style="font-size: 0.75rem;">
                    Kelompok {{ $currentClassIndex ?? 1 }} dari {{ $totalClasses ?? 1 }} ({{ $alumni->count() }} siswa lulus)
                </small>
            </div>

            <div>
                @if(!empty($nextClass))
                    <a href="{{ route('admin.alumni.index', ['class' => $nextClass]) }}" class="btn btn-sm btn-dark rounded-pill px-3 fw-semibold" style="background: #16233F;">
                        Kelas Selanjutnya ({{ $nextClass }}) <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @else
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" disabled>
                        Kelas Selanjutnya <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                @endif
            </div>
        </div>
    @elseif(request('search'))
        <div class="alert alert-info py-2 px-3 rounded-4 mb-3 d-flex align-items-center justify-content-between small border-0 shadow-sm" style="background: rgba(59, 130, 246, 0.1); color: #1E40AF;">
            <div>
                <i class="bi bi-info-circle-fill me-1"></i> Menampilkan hasil pencarian alumni <strong>"{{ request('search') }}"</strong> di <strong>seluruh kelas</strong> (ditemukan {{ $alumni->count() }} siswa lulus).
            </div>
            <a href="{{ route('admin.alumni.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.75rem;">
                <i class="bi bi-x-circle me-1"></i> Tutup Pencarian
            </a>
        </div>
    @endif

    {{-- Data Table --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Rekening</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Status / Angkatan Lulus</th>
                    <th>Saldo Tersimpan</th>
                    <th class="text-center" style="width: 240px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumni as $item)
                    <tr>
                        <td>
                            <span class="fw-bold text-dark font-monospace">{{ $item->account_number ?: 'Belum Ada Rekening' }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-secondary font-monospace">{{ $item->student_id }}</span>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $item->student_name }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border px-3 py-1 rounded-pill">
                                <i class="bi bi-mortarboard-fill text-primary me-1"></i> {{ $item->grade_class }}
                            </span>
                        </td>
                        <td>
                            <span class="fw-bold text-success">Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex gap-2 align-items-center">
                                <a href="{{ route('admin.savers.history', $item->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" title="Lihat Riwayat Transaksi">
                                    <i class="bi bi-clock-history me-1"></i> Riwayat
                                </a>
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalReactivate{{ $item->id }}">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Aktifkan Lagi
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-mortarboard display-6 d-block mb-2 opacity-50"></i>
                            <span class="fw-semibold">Belum ada data siswa yang berstatus lulus (alumni).</span>
                            <p class="small mb-0 mt-1">Ketika siswa Kelas XII (Kelas 3) dinaikkan kelas melalui fitur Naik Kelas Massal, data mereka otomatis dipindahkan ke halaman ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modals Aktifkan Kembali --}}
@foreach($alumni as $item)
<div class="modal fade" id="modalReactivate{{ $item->id }}" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 22px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.22);">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold text-dark">
                    <i class="bi bi-arrow-counterclockwise text-success me-2"></i>Aktifkan Kembali Nasabah
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <form action="{{ route('admin.alumni.reactivate', $item->id) }}" method="POST">
                @csrf
                <div class="modal-body px-4 py-3">
                    <p class="text-muted small mb-3">
                        Anda akan mengaktifkan kembali akun nasabah berikut beserta seluruh saldo dan riwayat transaksinya:
                    </p>
                    <div class="p-3 rounded-3 bg-light border mb-3 small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Nama Siswa:</span>
                            <strong class="text-dark">{{ $item->student_name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">NIS / No. Rekening:</span>
                            <strong class="text-dark">{{ $item->student_id }} / {{ $item->account_number ?: '-' }}</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Saldo Tersimpan:</span>
                            <strong class="text-success">Rp {{ number_format($item->saldo ?? 0, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-semibold small text-dark">Kembalikan ke Kelas Aktif</label>
                        <input type="text" name="grade_class" class="form-control" value="{{ trim(preg_replace('/^Lulus\s+\d{4}\s*/i', 'XII ', $item->grade_class)) ?: 'XII' }}" required>
                        <div class="form-text">Contoh: XII RPL 1, XII AKL 1, dll.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-1 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">
                        <i class="bi bi-check-circle-fill me-1"></i> Ya, Aktifkan Kembali
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach
@endsection

