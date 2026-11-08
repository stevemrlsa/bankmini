@extends('layouts.app')

@section('title', 'Janjian Penarikan Dana Besar — Bank Mini')
@section('header_title', 'Janjian Penarikan Dana Besar')
@section('page-title', 'Janjian Penarikan Dana Besar (> Rp 1.000.000)')
@section('page-subtitle', 'Manajemen reservasi penarikan dana besar dengan otorisasi ganda Teller & Admin minimal H-1')

@push('styles')
<style>
    .stat-card-glass {
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 20px;
        padding: 20px 24px;
        box-shadow: 0 10px 24px rgba(22, 35, 63, 0.04);
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .stat-icon-wrap {
        width: 50px;
        height: 50px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .toolbar-card {
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 20px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 20px;
        box-shadow: 0 8px 24px rgba(22, 35, 63, 0.05);
    }

    .filter-select {
        border-radius: 12px;
        border: 1px solid rgba(22, 35, 63, 0.12);
        padding: 9px 14px;
        font-size: 0.85rem;
        background: #fff;
        color: #1C2536;
        outline: none;
    }

    .table-premium-wrap {
        border-radius: 20px;
        overflow: hidden;
        background: #fff;
        border: 1px solid rgba(22, 35, 63, 0.07);
        box-shadow: 0 10px 26px rgba(22, 35, 63, 0.05);
    }

    table.table-premium thead th {
        background: #F8FAFC;
        color: #5C6579;
        font-weight: 600;
        font-size: 0.74rem;
        border: none;
        padding: 14px 18px;
        white-space: nowrap;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    table.table-premium tbody td {
        padding: 15px 18px;
        vertical-align: middle;
        border-top: 1px solid rgba(22, 35, 63, 0.06);
        color: #1C2536;
        font-size: 0.86rem;
    }

    .btn-create-booking {
        background: linear-gradient(135deg, #B45309, #D97706);
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 12px;
        font-weight: 600;
        font-size: 0.86rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 8px 20px rgba(180, 83, 9, 0.25);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-create-booking:hover {
        color: #fff;
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(180, 83, 9, 0.35);
    }
</style>
@endpush

@section('content')

    <!-- Flash Alert -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    @endif

    <!-- Kartu Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-md-4">
            <div class="stat-card-glass">
                <div class="stat-icon-wrap" style="background: rgba(245, 158, 11, 0.12); color: #B45309;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Menunggu Otorisasi</div>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($countPending) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card-glass">
                <div class="stat-icon-wrap" style="background: rgba(59, 130, 246, 0.12); color: #2563EB;">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Disetujui (Siap Dicairkan)</div>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($countApproved) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="stat-card-glass">
                <div class="stat-icon-wrap" style="background: rgba(16, 185, 129, 0.12); color: #059669;">
                    <i class="bi bi-check2-circle"></i>
                </div>
                <div>
                    <div class="text-muted small fw-medium">Selesai Dicairkan</div>
                    <h3 class="fw-bold mb-0 text-success">{{ number_format($countCompleted) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Toolbar: Filter & Tombol Buat Janjian -->
    <div class="toolbar-card">
        @php
            $currentRoute = request()->routeIs('teller.*') ? 'teller.withdrawal-requests.index' : 'admin.withdrawal-requests.index';
            $createRoute  = request()->routeIs('teller.*') ? 'teller.withdrawal-requests.create' : 'admin.withdrawal-requests.create';
            $isAdmin      = in_array(auth()->user()->role, ['Administrator', 'Admin', 'Admin Utama', 'admin_utama', 'Supervisor']);
            $isTeller     = (auth()->user()->role === 'Teller');
        @endphp

        <form method="GET" action="{{ route($currentRoute) }}" class="d-flex gap-2 flex-wrap align-items-center mb-0">
            <div class="input-group" style="max-width: 320px;">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    style="border-radius: 12px 0 0 12px; font-size: 0.85rem; border-color: rgba(22, 35, 63, 0.12);"
                    placeholder="Cari kode booking, nama, NIS..."
                    value="{{ request('search') }}"
                >
                <button type="submit" class="btn btn-dark" style="border-radius: 0 12px 12px 0; padding: 8px 16px;" title="Cari Data">
                    <i class="bi bi-search"></i>
                </button>
            </div>

            <select name="status" class="filter-select" onchange="this.form.submit()">
                <option value="all">Semua Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Menunggu Review</option>
                <option value="approved_teller" {{ request('status') === 'approved_teller' ? 'selected' : '' }}>Disetujui Teller (Menunggu Admin)</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Disetujui Penuh (Siap Cair)</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai Dicairkan</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>

            @if(request('search') || (request('status') && request('status') !== 'all'))
                <a href="{{ route($currentRoute) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2 text-decoration-none" style="font-size: 0.78rem;">
                    <i class="bi bi-x-circle me-1"></i> Reset
                </a>
            @endif
        </form>

        <a href="{{ route($createRoute) }}" class="btn-create-booking">
            <i class="bi bi-calendar-plus-fill"></i> Buat Janjian Penarikan Baru
        </a>
    </div>

    <!-- Tabel Data Janjian Penarikan -->
    <div class="table-premium-wrap">
        <div class="table-responsive">
            <table class="table table-premium mb-0">
                <thead>
                    <tr>
                        <th>Kode Janjian</th>
                        <th>Nasabah</th>
                        <th>Nominal Penarikan</th>
                        <th>Tanggal Pengambilan</th>
                        <th>Keperluan</th>
                        <th class="text-center">Status Otorisasi</th>
                        <th class="text-center" style="min-width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr>
                        {{-- Kode Janjian --}}
                        <td>
                            <strong class="text-dark d-block">{{ $req->request_code }}</strong>
                            <small class="text-muted" style="font-size: 0.72rem;">
                                Diajukan: {{ $req->created_at ? $req->created_at->timezone('Asia/Jakarta')->format('d/m/Y H:i') : '-' }} WIB
                            </small>
                        </td>

                        {{-- Data Nasabah --}}
                        <td>
                            <div class="fw-semibold text-dark">{{ $req->saver->student_name ?? 'Nasabah' }}</div>
                            <div class="text-muted small" style="font-size: 0.74rem;">
                                Rek: <span class="fw-semibold text-secondary">{{ $req->saver->account_number ?? '-' }}</span> · {{ $req->saver->grade_class ?? '-' }}
                            </div>
                            <div class="text-success small fw-medium" style="font-size: 0.74rem;">
                                Saldo: Rp {{ number_format($req->saver->saldo ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        {{-- Nominal --}}
                        <td>
                            <span class="fw-bold text-danger fs-6">
                                Rp {{ number_format($req->amount, 0, ',', '.') }}
                            </span>
                            <small class="d-block text-muted" style="font-size: 0.7rem;">&gt; Rp 1.000.000</small>
                        </td>

                        {{-- Tanggal Janji Penarikan --}}
                        <td>
                            <div class="fw-semibold text-dark">
                                <i class="bi bi-calendar-event me-1 text-primary"></i>
                                {{ \Carbon\Carbon::parse($req->scheduled_date)->translatedFormat('d M Y') }}
                            </div>
                            @php
                                $diffDays = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($req->scheduled_date)->startOfDay(), false);
                            @endphp
                            @if($diffDays === 0)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0 rounded-pill" style="font-size: 0.7rem;">Hari Ini</span>
                            @elseif($diffDays > 0)
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-0 rounded-pill" style="font-size: 0.7rem;">H-{{ $diffDays }} hari lagi</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger px-2 py-0 rounded-pill" style="font-size: 0.7rem;">Sudah Lewat</span>
                            @endif
                        </td>

                        {{-- Keperluan --}}
                        <td style="max-width: 220px;">
                            <div class="small text-secondary" style="line-height: 1.4; word-wrap: break-word;">
                                {{ $req->purpose ?: '-' }}
                            </div>
                            @if($req->notes)
                                <div class="text-muted" style="font-size: 0.72rem;">Catatan: {{ $req->notes }}</div>
                            @endif
                        </td>

                        {{-- Status Otorisasi Ganda --}}
                        <td class="text-center">
                            @if($req->status === 'completed')
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-check-all me-1"></i>Selesai Dicairkan
                                </span>
                                @if($req->disbursedBy)
                                    <div class="text-muted" style="font-size: 0.7rem;">oleh: {{ $req->disbursedBy->officer_name ?? $req->disbursedBy->username }}</div>
                                @endif
                            @elseif($req->status === 'approved')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-shield-fill-check me-1"></i>Disetujui Penuh
                                </span>
                                <div class="text-muted" style="font-size: 0.7rem;">Siap dicairkan loket teller</div>
                            @elseif($req->status === 'approved_teller')
                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-person-check me-1"></i>Disetujui Teller
                                </span>
                                <div class="text-muted" style="font-size: 0.7rem;">Menunggu otorisasi Admin</div>
                            @elseif($req->status === 'rejected')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-x-circle-fill me-1"></i>Ditolak
                                </span>
                                @if($req->rejection_reason)
                                    <div class="text-danger" style="font-size: 0.7rem;">"{{ $req->rejection_reason }}"</div>
                                @endif
                            @elseif($req->status === 'cancelled')
                                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-slash-circle me-1"></i>Dibatalkan
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-3 py-2 rounded-pill fw-semibold">
                                    <i class="bi bi-hourglass-split me-1"></i>Menunggu Review
                                </span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center gap-1 flex-wrap">
                                {{-- Aksi Persetujuan Admin --}}
                                @if($isAdmin && in_array($req->status, ['pending', 'approved_teller']))
                                    @php $approveAdminRoute = request()->routeIs('teller.*') ? 'teller.withdrawal-requests.approve-admin' : 'admin.withdrawal-requests.approve-admin'; @endphp
                                    <form action="{{ route($approveAdminRoute, $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Otorisasi: Setujui permohonan penarikan dana besar Rp {{ number_format($req->amount, 0, ',', '.') }} untuk {{ $req->saver->student_name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-dark px-2 py-1 fw-semibold" style="font-size: 0.76rem; border-radius: 8px;">
                                            <i class="bi bi-shield-check me-1"></i> Setujui (Admin)
                                        </button>
                                    </form>
                                @endif

                                {{-- Aksi Persetujuan Teller --}}
                                @if($isTeller && $req->status === 'pending')
                                    @php $approveTellerRoute = request()->routeIs('teller.*') ? 'teller.withdrawal-requests.approve-teller' : 'admin.withdrawal-requests.approve-teller'; @endphp
                                    <form action="{{ route($approveTellerRoute, $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Konfirmasi: Review dan setujui permohonan penarikan Rp {{ number_format($req->amount, 0, ',', '.') }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary px-2 py-1 fw-semibold" style="font-size: 0.76rem; border-radius: 8px;">
                                            <i class="bi bi-check-lg me-1"></i> Setujui (Teller)
                                        </button>
                                    </form>
                                @endif

                                {{-- Tombol Langsung Cairkan (Jika Teller & Status Approved) --}}
                                @if($isTeller && $req->status === 'approved')
                                    <a href="{{ route('teller.penarikan.create') }}?account_number={{ $req->saver->account_number ?? $req->saver->student_id }}&amount={{ (int)$req->amount }}&request_id={{ $req->id }}" class="btn btn-sm btn-success px-2 py-1 fw-semibold shadow-sm" style="font-size: 0.76rem; border-radius: 8px;">
                                        <i class="bi bi-cash me-1"></i> Cairkan Uang
                                    </a>
                                @elseif($isAdmin && $req->status === 'approved')
                                    <span class="badge bg-light text-muted border px-2 py-1" style="font-size: 0.72rem;">
                                        <i class="bi bi-clock-history me-1"></i>Menunggu Teller
                                    </span>
                                @endif

                                {{-- Tombol Tolak --}}
                                @if(in_array($req->status, ['pending', 'approved_teller']))
                                    <button type="button" class="btn btn-sm btn-outline-danger px-2 py-1" style="font-size: 0.76rem; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#modalReject{{ $req->id }}" title="Tolak Pengajuan">
                                        <i class="bi bi-x-circle"></i> Tolak
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada pengajuan janjian penarikan dana besar (&gt; Rp 1.000.000).
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($requests, 'hasPages') && $requests->hasPages())
            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="small text-muted">Menampilkan {{ $requests->firstItem() }}–{{ $requests->lastItem() }} dari {{ $requests->total() }} pengajuan</span>
                <div>{{ $requests->links('pagination::bootstrap-5') }}</div>
            </div>
        @endif
    </div>

    <!-- Modals Penolakan -->
    @foreach($requests as $req)
        @if(in_array($req->status, ['pending', 'approved_teller']))
            @php $rejectRoute = request()->routeIs('teller.*') ? 'teller.withdrawal-requests.reject' : 'admin.withdrawal-requests.reject'; @endphp
            <div class="modal fade" id="modalReject{{ $req->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header border-0 pb-0 justify-content-between">
                            <h5 class="modal-title fw-bold text-danger">
                                <i class="bi bi-x-circle-fill me-2"></i> Tolak Janjian Penarikan
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <form action="{{ route($rejectRoute, $req->id) }}" method="POST">
                            @csrf
                            <div class="modal-body py-3">
                                <div class="p-3 rounded-3 mb-3 bg-light border small">
                                    <div>Kode: <strong>{{ $req->request_code }}</strong></div>
                                    <div>Nasabah: <strong>{{ $req->saver->student_name ?? '-' }}</strong> ({{ $req->saver->account_number ?? '-' }})</div>
                                    <div class="text-danger fw-bold mt-1">Nominal: Rp {{ number_format($req->amount, 0, ',', '.') }}</div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small fw-semibold text-dark">Alasan Penolakan <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" class="form-control rounded-3" rows="3" placeholder="Contoh: Ketersediaan uang brankas belum mencukupi pada tanggal tersebut, silakan jadwalkan ulang." required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm">
                                    <i class="bi bi-x-circle-fill me-1"></i> Simpan Penolakan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach

@endsection

