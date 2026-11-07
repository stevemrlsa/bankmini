@extends('layouts.app')

@section('header_title', 'Ringkasan Saldo Tersimpan')
@section('page-title', 'Ringkasan Saldo Tersimpan')
@section('page-subtitle', 'Rekapitulasi dana tabungan siswa per kelas, status nasabah, dan penabung tertinggi')

@section('content')
<div class="container-fluid px-0">
    {{-- Top Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 46px; height: 46px; background: linear-gradient(135deg, #B45309, #F59E0B);">
                        <i class="bi bi-wallet2 fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Total Seluruh Saldo Tersimpan</div>
                        <h4 class="fw-bold text-dark mb-0">Rp {{ number_format($totalSaldoAll ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <small class="text-muted">Akumulasi dana tabungan seluruh nasabah di sistem Bank Mini</small>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 46px; height: 46px; background: linear-gradient(135deg, #0F766E, #14B8A6);">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Saldo Nasabah Aktif ({{ number_format($jumlahNasabahAktif ?? 0) }} Siswa)</div>
                        <h4 class="fw-bold text-success mb-0">Rp {{ number_format($saldoNasabahAktif ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted">Siswa aktif kelas X, XI, XII</small>
                    <a href="{{ route('admin.savers.index') }}" class="small fw-semibold text-decoration-none">Lihat Nasabah &rarr;</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 20px;">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <div class="rounded-3 d-flex align-items-center justify-content-center text-white" style="width: 46px; height: 46px; background: linear-gradient(135deg, #16233F, #3B5BA5);">
                        <i class="bi bi-mortarboard-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold">Saldo Mengendap Alumni ({{ number_format($jumlahAlumni ?? 0) }} Lulus)</div>
                        <h4 class="fw-bold text-primary mb-0">Rp {{ number_format($saldoAlumni ?? 0, 0, ',', '.') }}</h4>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <small class="text-muted">Dana siswa lulus yang masih tersimpan</small>
                    <a href="{{ route('admin.alumni.index') }}" class="small fw-semibold text-decoration-none">Lihat Alumni &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Rekap Saldo Per Kelas --}}
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="bi bi-bar-chart-fill text-primary me-2"></i>Rekapitulasi Saldo per Kelas</h5>
                        <small class="text-muted">Distribusi simpanan tabungan berdasarkan kelas</small>
                    </div>
                    <a href="{{ route('admin.journals.index') }}" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-semibold">
                        <i class="bi bi-journal-bookmark-fill me-1"></i> Buka Jurnal Akuntansi
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Kelas</th>
                                <th class="text-center">Nasabah Aktif</th>
                                <th class="text-end">Total Saldo Kelas</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classRecap as $row)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $row->class_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3">{{ $row->active_students }} siswa</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($row->total_balance ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        @if(\Illuminate\Support\Str::startsWith($row->class_name, 'Lulus'))
                                            <a href="{{ route('admin.alumni.index', ['class' => $row->class_name]) }}" class="btn btn-sm btn-light border rounded-pill px-3 small">Detail</a>
                                        @else
                                            <a href="{{ route('admin.savers.index', ['class' => $row->class_name]) }}" class="btn btn-sm btn-light border rounded-pill px-3 small">Detail</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted py-4">Belum ada data saldo tersimpan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- 10 Penabung Tertinggi --}}
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 20px;">
                <div class="mb-3">
                    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-trophy-fill text-warning me-2"></i>10 Penabung Saldo Tertinggi</h5>
                    <small class="text-muted">Nasabah dengan saldo tabungan terbesar saat ini</small>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nasabah</th>
                                <th>Kelas</th>
                                <th class="text-end">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topSavers as $idx => $saver)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $idx + 1 }}. {{ $saver->student_name }}</div>
                                        <small class="text-muted font-monospace">{{ $saver->account_number ?: $saver->student_id }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border rounded-pill">{{ $saver->grade_class }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rp {{ number_format($saver->saldo ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">Belum ada penabung dengan saldo aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

