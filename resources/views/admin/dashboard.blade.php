@extends('layouts.app')

@section('title', 'Dashboard Administrator — Bank Mini')
@section('header_title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Ringkasan operasional Bank Mini hari ini')

@push('styles')
<style>
    /* ===== Sapaan Header ===== */
    .greeting-banner {
        border-radius: 22px;
        padding: 30px 34px;
        background: linear-gradient(135deg, #16233F 0%, #24355A 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 26px;
        box-shadow: 0 16px 36px rgba(22, 35, 63, 0.22);
    }

    .greeting-banner::after {
        content: '';
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        right: -70px;
        top: -90px;
    }

    .greeting-banner h2 {
        font-size: 1.4rem;
        font-weight: 600;
        margin-bottom: 6px;
        position: relative;
        z-index: 1;
    }

    .greeting-banner p {
        font-size: 0.86rem;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
        position: relative;
        z-index: 1;
        max-width: 480px;
    }

    .greeting-banner .greeting-date {
        position: absolute;
        right: 34px;
        top: 30px;
        text-align: right;
        z-index: 1;
    }

    .greeting-banner .greeting-date strong {
        display: block;
        font-size: 0.92rem;
        font-weight: 500;
    }

    .greeting-banner .greeting-date span {
        font-size: 0.72rem;
        color: rgba(255, 255, 255, 0.75);
    }

    /* ===== Stat Cards ===== */
    .stat-card {
        background: #FFFFFF;
        border: 1px solid #EEF2F6;
        border-radius: 20px;
        padding: 22px 24px;
        box-shadow: 0 4px 18px rgba(12, 96, 240, 0.04);
        height: 100%;
        transition: transform 0.18s ease, box-shadow 0.18s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px rgba(12, 96, 240, 0.1);
    }

    .stat-card .stat-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 18px;
    }

    .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.15rem;
        color: #fff;
    }

    .stat-icon.bg-navy { background: linear-gradient(135deg, #16233F, #2A3E63); }
    .stat-icon.bg-teal { background: linear-gradient(135deg, #0F766E, #14B8A6); }
    .stat-icon.bg-amber { background: linear-gradient(135deg, #B45309, #F59E0B); }
    .stat-icon.bg-plum { background: linear-gradient(135deg, #6D28D9, #9F7AEA); }

    .stat-trend {
        font-size: 0.72rem;
        font-weight: 500;
        padding: 4px 9px;
        border-radius: 20px;
    }

    .stat-trend.up { background: rgba(22, 163, 74, 0.12); color: #15803D; }
    .stat-trend.down { background: rgba(220, 38, 38, 0.1); color: #B91C1C; }

    .stat-value {
        font-size: 1.55rem;
        font-weight: 600;
        color: #1C2536;
        line-height: 1.1;
        margin-bottom: 3px;
    }

    .stat-label {
        font-size: 0.8rem;
        color: #808C9E;
    }

    /* ===== Panel bawah ===== */
    .panel-title {
        font-size: 0.98rem;
        font-weight: 600;
        color: #1C2536;
        margin-bottom: 2px;
    }

    .panel-subtitle {
        font-size: 0.78rem;
        color: #808C9E;
    }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 0;
        border-bottom: 1px solid rgba(22, 35, 63, 0.06);
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-icon {
        width: 38px;
        height: 38px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
        flex-shrink: 0;
    }

    .activity-icon.in { background: rgba(22, 163, 74, 0.1); color: #15803D; }
    .activity-icon.out { background: rgba(220, 38, 38, 0.08); color: #B91C1C; }

    .activity-text strong {
        display: block;
        font-size: 0.84rem;
        font-weight: 500;
        color: #1C2536;
    }

    .activity-text span {
        font-size: 0.74rem;
        color: #808C9E;
    }

    .activity-amount {
        margin-left: auto;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .activity-amount.in { color: #15803D; }
    .activity-amount.out { color: #B91C1C; }

    .quick-role {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 14px;
        border-radius: 14px;
        background: rgba(244, 247, 246, 0.9);
        margin-bottom: 10px;
    }

    .quick-role:last-child { margin-bottom: 0; }

    .quick-role .role-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
    }

    .quick-role span.count {
        margin-left: auto;
        font-weight: 600;
        color: #1C2536;
        font-size: 0.85rem;
    }
</style>
@endpush

@section('content')

    <!-- Banner Sapaan -->
    <div class="greeting-banner">
        <h2>Selamat datang kembali, {{ Auth::user()->officer_name ?? Auth::user()->username ?? 'Admin' }} 👋</h2>
        <p>Berikut ringkasan aktivitas Bank Mini Sekolah untuk periode berjalan. Semua data diperbarui secara real-time.</p>
        <div class="greeting-date">
            <strong>{{ now()->translatedFormat('d F Y') }}</strong>
            <span>{{ now()->translatedFormat('l, H:i') }} WIB</span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon bg-navy"><i class="bi bi-person-badge-fill"></i></div>
                    <span class="stat-trend up"><i class="bi bi-arrow-up-short"></i></span>
                </div>
                <div class="stat-value">{{ number_format($totalPegawai ?? 0) }}</div>
                <div class="stat-label">Total Pegawai</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon bg-teal"><i class="bi bi-people-fill"></i></div>
                    <span class="stat-trend up"><i class="bi bi-arrow-up-short"></i></span>
                </div>
                <div class="stat-value">{{ number_format($totalNasabahAktif ?? 0) }}</div>
                <div class="stat-label">Nasabah Aktif <small class="text-muted fw-normal" style="font-size: 0.72rem;">({{ number_format($totalNasabah ?? 0) }} total)</small></div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon bg-amber"><i class="bi bi-wallet2"></i></div>
                    <span class="stat-trend up"><i class="bi bi-arrow-up-short"></i></span>
                </div>
                <div class="stat-value">Rp {{ number_format($totalSaldo ?? 0, 0, ',', '.') }}</div>
                <div class="stat-label">Total Saldo Tersimpan</div>
            </div>
        </div>

        <div class="col-6 col-lg-3">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon bg-plum"><i class="bi bi-arrow-left-right"></i></div>
                    <span class="stat-trend up"><i class="bi bi-arrow-up-short"></i></span>
                </div>
                <div class="stat-value">{{ number_format($transaksiHariIni ?? 0) }}</div>
                <div class="stat-label">Transaksi Hari Ini</div>
            </div>
        </div>
    </div>

    <!-- Panel bawah -->
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card-solid p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="panel-title">Transaksi Terbaru</p>
                        <p class="panel-subtitle">5 transaksi terakhir di seluruh loket</p>
                    </div>
                </div>

                <div class="activity-list">
                    @forelse($latestTransactions as $transaction)
                        @php
                            $isDeposit = in_array(strtolower($transaction->transaction_type), ['deposit', 'setor', 'setoran']);
                        @endphp
                        <div class="activity-item">
                            @if($isDeposit)
                                <div class="activity-icon in"><i class="bi bi-arrow-down-left"></i></div>
                            @else
                                <div class="activity-icon out"><i class="bi bi-arrow-up-right"></i></div>
                            @endif
                            <div class="activity-text">
                                <strong>{{ $transaction->saver->student_name ?? $transaction->saver->name ?? 'Nasabah' }} — {{ $transaction->saver->grade_class ?? $transaction->saver->student_class ?? $transaction->saver->student_id ?? '-' }}</strong>
                                <span>{{ $isDeposit ? 'Setoran' : 'Penarikan' }} tunai · {{ $transaction->created_at ? $transaction->created_at->format('H:i') : '-' }}</span>
                            </div>
                            @if($isDeposit)
                                <div class="activity-amount in text-success">+Rp {{ number_format($transaction->amount, 0, ',', '.') }}</div>
                            @else
                                <div class="activity-amount out text-danger">-Rp {{ number_format($transaction->amount, 0, ',', '.') }}</div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center text-muted p-4">
                            Belum ada transaksi hari ini.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card-solid p-4">
                <p class="panel-title mb-1">Distribusi Pegawai</p>
                <p class="panel-subtitle mb-3">Berdasarkan role sistem</p>

                <div class="quick-role">
                    <span class="role-dot" style="background:#16233F;"></span>
                    Administrator
                    <span class="count">{{ number_format($countAdmin ?? 0) }}</span>
                </div>
                <div class="quick-role">
                    <span class="role-dot" style="background:#14B8A6;"></span>
                    Teller
                    <span class="count">{{ number_format($countTeller ?? 0) }}</span>
                </div>
            </div>
        </div>
    </div>

@endsection
