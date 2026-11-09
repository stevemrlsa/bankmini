@extends('layouts.app')

@section('title', 'Approval Kas & Shift Laci Teller')
@section('header_title', 'Approval Kas Teller')
@section('page-title', 'Approval Kas Teller')
@section('page-subtitle', 'Rekonsiliasi modal awal, laci kasir teller, dan otorisasi pemindahan kas fisik ke Brankas Utama (Vault Transfer)')

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

    {{-- Ringkasan Statistik Kartu Kas Teller --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #EFF6FF; color: #1E3A8A;">
                        <i class="bi bi-inboxes-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Laporan</div>
                        <h5 class="fw-bold text-dark mb-0">{{ $reports->count() }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #FEF3C7; color: #D97706;">
                        <i class="bi bi-hourglass-split fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Menunggu Verifikasi</div>
                        <h5 class="fw-bold text-warning-emphasis mb-0">{{ $reports->where('status', 'pending')->count() }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #DCFCE7; color: #16A34A;">
                        <i class="bi bi-shield-check fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Disetujui (Di Brankas)</div>
                        <h5 class="fw-bold text-success mb-0">{{ $reports->where('status', 'approved')->count() }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 16px; background: #ffffff;">
                <div class="card-body p-3 d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #FEE2E2; color: #DC2626;">
                        <i class="bi bi-x-circle-fill fs-5"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Laporan Ditolak</div>
                        <h5 class="fw-bold text-danger mb-0">{{ $reports->where('status', 'rejected')->count() }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Table Card --}}
    <div class="card border-0 shadow-sm" style="border-radius: 20px; overflow: hidden; background: #ffffff;">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-shield-check text-primary me-2"></i>Daftar Approval & Rekonsiliasi Kas Teller
                </h5>
                <small class="text-muted">Pemeriksaan modal kas awal, mutasi laci kasir, fisik kas vs kas sistem sebelum dipindah ke Brankas Utama</small>
            </div>
            <div>
                <span class="badge bg-light text-dark border px-3 py-2" style="border-radius: 10px; font-weight: 500;">
                    <i class="bi bi-clock-history me-1 text-primary"></i> Data Terkini: {{ $reports->count() }} Laporan
                </span>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                    <thead style="background-color: #F8FAFC;">
                        <tr>
                            <th class="py-3 px-4 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em; white-space: nowrap; min-width: 130px;">Tanggal & Waktu</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em; white-space: nowrap; min-width: 150px;">Teller</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em; white-space: nowrap; min-width: 120px;">Kas Awal (Modal)</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em; white-space: nowrap; min-width: 120px;">Kas Sistem</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em; white-space: nowrap; min-width: 160px;">Kas Fisik di Laci</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em; white-space: nowrap; min-width: 140px;">Selisih (Fisik - Sistem)</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold" style="font-size: 0.74rem; letter-spacing: 0.05em; min-width: 300px; width: 380px;">Catatan Rekonsiliasi</th>
                            <th class="py-3 text-secondary text-uppercase fw-semibold text-center" style="font-size: 0.74rem; letter-spacing: 0.05em; white-space: nowrap; min-width: 140px;">Status</th>
                            <th class="py-3 px-4 text-secondary text-uppercase fw-semibold text-center" style="font-size: 0.74rem; letter-spacing: 0.05em; min-width: 200px; white-space: nowrap;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reports as $report)
                            @php
                                $isAutoReport = is_null($report->closing_balance_physical);
                                $fisik = (float) ($report->closing_balance_physical ?? 0);
                                $sistem = (float) ($report->closing_balance_system ?? 0);
                                $diff = (int) round($fisik - $sistem);
                                $timeStr = $report->created_at ? \Carbon\Carbon::parse($report->created_at)->timezone('Asia/Jakarta')->format('H:i') : null;
                            @endphp
                            <tr style="border-bottom: 1px solid #F1F5F9;">
                                {{-- Tanggal & Waktu --}}
                                <td class="px-4 py-3" style="white-space: nowrap;">
                                    <div class="fw-semibold text-dark">
                                        {{ \Carbon\Carbon::parse($report->report_date ?? $report->created_at)->timezone('Asia/Jakarta')->translatedFormat('d M Y') }}
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-clock me-1"></i>{{ $timeStr ? $timeStr . ' WIB' : 'Hari ini' }}
                                    </small>
                                </td>

                                {{-- Nama Teller --}}
                                <td class="py-3" style="white-space: nowrap;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold shadow-sm" 
                                             style="width: 34px; height: 34px; background: #EFF6FF; color: #1E3A8A; border: 1px solid #DBEAFE; font-size: 0.82rem;">
                                            {{ strtoupper(substr($report->teller->officer_name ?? $report->teller->username ?? 'T', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold text-dark d-flex align-items-center gap-1">
                                                {{ $report->teller->officer_name ?? $report->teller->username ?? 'Teller' }}
                                                @if(str_contains($report->notes ?? '', 'Shift Bersama') || ($report->shift && count($report->shift->getParticipatingUserIds()) > 1))
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0" style="font-size: 0.68rem;">
                                                        <i class="bi bi-people-fill me-1"></i>Shift Bersama
                                                    </span>
                                                @endif
                                            </div>
                                            <small class="text-muted">{{ $report->teller->role ?? 'Teller' }}</small>
                                        </div>
                                    </div>
                                </td>

                                {{-- Kas Awal (Modal) - PENGGABUNGAN FITUR SHIFT LACI KASIR --}}
                                <td class="py-3" style="white-space: nowrap;">
                                    <div class="fw-semibold text-secondary">
                                        Rp {{ number_format($report->opening_balance ?? 0, 0, ',', '.') }}
                                    </div>
                                    <small class="text-muted" style="font-size: 0.72rem;">Modal buka laci</small>
                                </td>

                                {{-- Kas Sistem --}}
                                <td class="py-3" style="white-space: nowrap;">
                                    <div class="fw-semibold text-dark">
                                        Rp {{ number_format($report->closing_balance_system ?? 0, 0, ',', '.') }}
                                    </div>
                                    @if(($report->total_deposits ?? 0) > 0 || ($report->total_withdrawals ?? 0) > 0)
                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            <span class="text-success">+{{ number_format($report->total_deposits, 0, ',', '.') }}</span> / 
                                            <span class="text-danger">-{{ number_format($report->total_withdrawals, 0, ',', '.') }}</span>
                                        </small>
                                    @endif
                                </td>

                                {{-- Kas Fisik di Laci --}}
                                <td class="py-3" style="white-space: nowrap;">
                                    @if($isAutoReport)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-robot me-1"></i>(Kosong - Input Otomatis)
                                        </span>
                                        <div class="text-muted" style="font-size: 0.72rem;">Fisik belum dihitung teller</div>
                                    @else
                                        <div class="fw-bold text-primary fs-6">
                                            Rp {{ number_format($report->closing_balance_physical ?? 0, 0, ',', '.') }}
                                        </div>
                                        <small class="text-muted" style="font-size: 0.72rem;">Hitungan fisik laci</small>
                                    @endif
                                </td>

                                {{-- Selisih Kas --}}
                                <td class="py-3" style="white-space: nowrap;">
                                    @if($isAutoReport)
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-dash-circle me-1"></i>Menunggu Fisik
                                        </span>
                                    @elseif($diff === 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-check-circle me-1"></i>Rp 0 (Balance)
                                        </span>
                                    @elseif($diff > 0)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-arrow-up-circle me-1"></i>+ Rp {{ number_format($diff, 0, ',', '.') }} (Lebih)
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill fw-semibold">
                                            <i class="bi bi-arrow-down-circle me-1"></i>- Rp {{ number_format(abs($diff), 0, ',', '.') }} (Kurang)
                                        </span>
                                    @endif
                                </td>

                                {{-- Catatan Rekonsiliasi --}}
                                <td class="py-3" style="min-width: 300px; max-width: 420px;">
                                    <div class="text-secondary small" style="line-height: 1.5; word-wrap: break-word; white-space: normal;">
                                        {{ $report->notes ?: '-' }}
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="py-3 text-center" style="white-space: nowrap;">
                                    @if($report->status === 'approved')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-check2-all me-1"></i>Disetujui (Vault)
                                        </span>
                                        @if($report->admin)
                                            <div class="text-muted" style="font-size: 0.72rem;">
                                                oleh: {{ $report->admin->officer_name ?? $report->admin->username }}
                                            </div>
                                        @endif
                                    @elseif($report->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-x-circle me-1"></i>Ditolak
                                        </span>
                                        @if($report->admin)
                                            <div class="text-muted" style="font-size: 0.72rem;">
                                                oleh: {{ $report->admin->officer_name ?? $report->admin->username }}
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">
                                            <i class="bi bi-hourglass-split me-1"></i>Menunggu Approval
                                        </span>
                                    @endif
                                </td>

                                {{-- Aksi --}}
                                <td class="px-4 py-3 text-center" style="white-space: nowrap; min-width: 200px;">
                                    <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                        @if($report->status === 'pending')
                                            {{-- Tombol Setujui (Pindah ke Brankas) --}}
                                            <form method="POST" action="{{ route('admin.approval.approve', $report->id) }}" onsubmit="return confirm('Konfirmasi Approval: Setujui laporan kas ini dan pindahkan kas fisik sebesar Rp {{ number_format($report->closing_balance_physical ?? 0, 0, ',', '.') }} ke Brankas Utama (Jurnal Akun 102)?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-dark px-3 py-1 fw-semibold shadow-sm" style="border-radius: 8px; background-color: #111827; border: none;" title="Setujui dan Pindahkan ke Brankas">
                                                    <i class="bi bi-safe me-1"></i> Setujui
                                                </button>
                                            </form>

                                            {{-- Tombol Tolak --}}
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1 fw-semibold" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalReject{{ $report->id }}" title="Tolak Laporan">
                                                <i class="bi bi-x-lg"></i> Tolak
                                            </button>
                                        @elseif($report->status === 'approved')
                                            <div class="d-inline-flex align-items-center gap-1 text-success fw-semibold small me-1">
                                                <i class="bi bi-shield-fill-check fs-5"></i>
                                                <span>Di Brankas</span>
                                            </div>
                                        @elseif($report->status === 'rejected')
                                            <div class="d-inline-flex align-items-center gap-1 text-danger fw-semibold small me-1">
                                                <i class="bi bi-x-circle-fill"></i>
                                                <span>Ditolak</span>
                                            </div>
                                        @endif

                                        {{-- Tombol Unduh PDF untuk Setiap Laporan Kas --}}
                                        <a href="{{ route('admin.approval.pdf', $report->id) }}" class="btn btn-sm btn-outline-primary px-2 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm" style="border-radius: 8px;" title="Unduh Berita Acara Kas PDF">
                                            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> PDF
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <div class="mb-3">
                                        <i class="bi bi-inbox fs-1 text-secondary opacity-50"></i>
                                    </div>
                                    <h6 class="fw-semibold text-secondary">Belum ada data laporan penutupan kas dari teller</h6>
                                    <p class="small text-muted mb-0">Laporan penutupan kas dan modal awal yang dikirim oleh Teller akan otomatis tampil di sini untuk diverifikasi dan diotorisasi Administrator.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ======================================================== --}}
{{-- MODALS SECTION: Placed OUTSIDE the card and table        --}}
{{-- to completely eliminate Bootstrap z-index/backdrop bugs  --}}
{{-- ======================================================== --}}
@foreach($reports as $report)
    @if($report->status === 'pending')
    <!-- Modal Tolak Laporan -->
    <div class="modal fade" id="modalReject{{ $report->id }}" tabindex="-1" aria-labelledby="modalRejectLabel{{ $report->id }}" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-danger-subtle p-2 text-danger d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-x-circle-fill fs-5"></i>
                        </div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="modalRejectLabel{{ $report->id }}">
                            Tolak Laporan Kas Teller
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('admin.approval.reject', $report->id) }}" method="POST">
                    @csrf
                    <div class="modal-body py-3 px-4">
                        <div class="alert alert-warning py-2 px-3 small rounded-3 mb-3">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            Laporan dari teller <strong>{{ $report->teller->officer_name ?? $report->teller->username ?? 'Teller' }}</strong> akan ditolak dan dikembalikan agar teller dapat menghitung ulang kas laci.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">Alasan / Catatan Penolakan <span class="text-danger">*</span></label>
                            <textarea name="notes" class="form-control rounded-3" rows="3" placeholder="Contoh: Terjadi selisih uang fisik vs sistem belum jelas, mohon hitung kembali kas laci..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm">
                            <i class="bi bi-x-circle-fill me-1"></i> Tolak Laporan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
@endforeach

<script>
    // Safeguard backdrop dismissal for Bootstrap 5
    document.addEventListener('DOMContentLoaded', function () {
        const rejectModals = document.querySelectorAll('.modal');
        rejectModals.forEach(function (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function () {
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
