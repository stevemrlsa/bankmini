@extends('layouts.app')

@section('title', 'Review & Approval Shift Kasir Teller')
@section('header_title', 'Shift Laci Kasir')
@section('page-title', 'Approval Shift Kasir Teller')
@section('page-subtitle', 'Verifikasi penutupan laci kas harian Teller dan otorisasi pemindahan dana fisik ke Brankas Utama')

@section('content')
<div class="container-fluid px-0">
    {{-- Notifikasi Sukses / Error --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 14px;" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 14px;" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Main Card --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-inboxes text-primary me-2"></i>Daftar Penutupan Shift Teller
                </h5>
                <small class="text-muted">Pemeriksaan rekonsiliasi kas fisik vs kas sistem sebelum dipindah ke Brankas Utama</small>
            </div>
            <div>
                <span class="badge bg-light text-dark border px-3 py-2" style="border-radius: 10px; font-weight: 500;">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Total Data: {{ $shifts->count() }} Shift
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                    <thead style="background-color: #F8FAFC;">
                        <tr>
                            <th class="py-3 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Tanggal & Waktu</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Nama Teller</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Modal Awal</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Kas Sistem</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Kas Fisik</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.75rem; letter-spacing: 0.05em;">Selisih (Fisik - Sistem)</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 0.75rem; letter-spacing: 0.05em;">Status</th>
                            <th class="py-3 px-4 text-secondary text-uppercase fw-semibold text-center" style="font-size: 0.75rem; letter-spacing: 0.05em; min-width: 200px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                            @php
                                $fisik = $shift->closing_balance ?? 0;
                                $sistem = $shift->system_balance ?? 0;
                                $selisih = $fisik - $sistem;
                            @endphp
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                {{-- Tanggal --}}
                                <td class="px-4 py-3">
                                    <div class="fw-semibold text-dark">
                                        {{ \Carbon\Carbon::parse($shift->closed_at ?? $shift->updated_at)->translatedFormat('d M Y') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ \Carbon\Carbon::parse($shift->closed_at ?? $shift->updated_at)->format('H:i') }} WIB
                                    </small>
                                </td>

                                {{-- Nama Teller --}}
                                <td class="py-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" 
                                             style="width: 34px; height: 34px; background: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE; font-size: 0.82rem;">
                                            {{ strtoupper(substr($shift->user->officer_name ?? $shift->user->username ?? 'T', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark">{{ $shift->user->officer_name ?? $shift->user->username ?? 'Teller' }}</div>
                                            <small class="text-muted">{{ $shift->user->role ?? 'Teller' }}</small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Modal Awal --}}
                                <td class="py-3">
                                    <span class="text-muted">Rp {{ number_format($shift->opening_balance, 0, ',', '.') }}</span>
                                </td>

                                {{-- Kas Sistem --}}
                                <td class="py-3">
                                    <span class="fw-semibold text-dark">Rp {{ number_format($shift->system_balance ?? 0, 0, ',', '.') }}</span>
                                </td>

                                {{-- Kas Fisik --}}
                                <td class="py-3">
                                    <span class="fw-bold text-dark">Rp {{ number_format($shift->closing_balance ?? 0, 0, ',', '.') }}</span>
                                </td>

                                {{-- Selisih (Fisik - Sistem) --}}
                                <td class="py-3">
                                    @if($selisih === 0)
                                        <span class="text-muted fw-semibold">
                                            <i class="bi bi-check-circle me-1 text-success"></i>Rp 0 (Balance)
                                        </span>
                                    @elseif($selisih > 0)
                                        <span class="text-success fw-bold">
                                            <i class="bi bi-arrow-up-circle me-1"></i>+ Rp {{ number_format($selisih, 0, ',', '.') }} (Lebih)
                                        </span>
                                    @else
                                        <span class="text-danger fw-bold">
                                            <i class="bi bi-arrow-down-circle me-1"></i>- Rp {{ number_format(abs($selisih), 0, ',', '.') }} (Kurang)
                                        </span>
                                    @endif
                                </td>

                                {{-- Status --}}
                                <td class="py-3 text-center">
                                    @if($shift->status === 'Approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-check2-all me-1"></i>Disetujui
                                        </span>
                                    @elseif($shift->status === 'Closed')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-hourglass-split me-1"></i>Menunggu Verifikasi
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold">
                                            {{ $shift->status }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-center">
                                    @if($shift->status === 'Closed')
                                        <form method="POST" action="{{ route('admin.shifts.approve', $shift->id) }}" onsubmit="return confirm('Konfirmasi: Setujui shift kas teller ini dan pindahkan kas fisik sebesar Rp {{ number_format($shift->closing_balance ?? 0, 0, ',', '.') }} ke Brankas Utama?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-dark px-3 py-2 fw-semibold shadow-sm" style="border-radius: 10px; background-color: #111827; border: none;">
                                                <i class="bi bi-safe me-1"></i> Setujui & Pindah ke Brankas
                                            </button>
                                        </form>
                                    @elseif($shift->status === 'Approved')
                                        <div class="d-inline-flex align-items-center gap-1 text-success fw-semibold small">
                                            <i class="bi bi-shield-fill-check fs-5"></i>
                                            <span>Selesai (Di Brankas)</span>
                                        </div>
                                        @if($shift->approver)
                                            <div class="text-muted" style="font-size: 0.75rem;">
                                                oleh: {{ $shift->approver->officer_name ?? $shift->approver->username }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="mb-3">
                                        <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                    </div>
                                    <h6 class="fw-semibold text-secondary">Belum ada data shift yang ditutup</h6>
                                    <p class="small text-muted mb-0">Shift kasir yang telah ditutup (Closed) oleh Teller akan tampil di sini untuk diperiksa dan disetujui Administrator.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
