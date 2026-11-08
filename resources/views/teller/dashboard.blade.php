@extends('layouts.app')

@section('title', 'Dashboard Teller — Bank Mini')
@section('header_title', 'Dashboard')
@section('page-title', 'Dashboard Teller')
@section('page-subtitle', 'Loket layanan transaksi tabungan siswa')

@push('styles')
<style>
    /* ===== Greeting Banner ===== */
    .greeting-banner {
        border-radius: 22px;
        padding: 26px 30px;
        background: linear-gradient(135deg, #16233F 0%, #24355A 100%);
        color: #fff;
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        box-shadow: 0 16px 36px rgba(22, 35, 63, 0.22);
    }

    .greeting-banner::after {
        content: '';
        position: absolute;
        width: 240px;
        height: 240px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        right: -60px;
        top: -80px;
    }

    .greeting-banner h2 {
        font-size: 1.32rem;
        font-weight: 600;
        margin-bottom: 6px;
        position: relative;
        z-index: 1;
    }

    .greeting-banner p {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
        position: relative;
        z-index: 1;
        max-width: 460px;
    }

    .shift-chip {
        position: relative;
        background: rgba(255, 255, 255, 0.12);
        border: 1px solid rgba(255, 255, 255, 0.18);
        border-radius: 30px;
        padding: 7px 16px;
        font-size: 0.78rem;
        display: inline-flex;
        align-items: center;
        gap: 7px;
    }

    /* ===== Aksi Cepat ===== */
    .quick-action-card {
        display: flex;
        align-items: center;
        gap: 18px;
        padding: 22px 24px;
        border-radius: 20px;
        border: 1px solid rgba(22, 35, 63, 0.08);
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 26px rgba(22, 35, 63, 0.06);
        text-decoration: none;
        color: #1C2536;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        height: 100%;
    }

    .quick-action-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 34px rgba(22, 35, 63, 0.12);
        color: #1C2536;
    }

    .quick-action-icon {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.45rem;
        color: #fff;
        flex-shrink: 0;
    }

    .quick-action-icon.setor { background: linear-gradient(135deg, #0F766E, #14B8A6); }
    .quick-action-icon.tarik { background: linear-gradient(135deg, #B45309, #F59E0B); }

    .quick-action-text strong {
        display: block;
        font-size: 1rem;
        font-weight: 600;
        margin-bottom: 3px;
    }

    .quick-action-text span {
        font-size: 0.8rem;
        color: #808C9E;
    }

    .quick-action-arrow {
        margin-left: auto;
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: #EFF6FF;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #16233F;
        font-size: 0.9rem;
        flex-shrink: 0;
    }

    /* ===== Ringkasan mini ===== */
    .mini-stat {
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(22, 35, 63, 0.08);
        border-radius: 18px;
        padding: 18px 20px;
        height: 100%;
    }

    .mini-stat .mini-label {
        font-size: 0.78rem;
        color: #808C9E;
        margin-bottom: 6px;
    }

    .mini-stat .mini-value {
        font-size: 1.28rem;
        font-weight: 600;
        color: #1C2536;
    }

    .mini-stat .mini-value.green { color: #15803D; }
    .mini-stat .mini-value.red { color: #B91C1C; }

    /* ===== Riwayat mini ===== */
    .panel-title { font-size: 0.98rem; font-weight: 600; color: #1C2536; margin-bottom: 2px; }
    .panel-subtitle { font-size: 0.78rem; color: #808C9E; }

    .activity-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 13px 0;
        border-bottom: 1px solid rgba(22, 35, 63, 0.06);
    }

    .activity-item:last-child { border-bottom: none; }

    .activity-icon {
        width: 38px; height: 38px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.95rem; flex-shrink: 0;
    }

    .activity-icon.in { background: rgba(22, 163, 74, 0.1); color: #15803D; }
    .activity-icon.out { background: rgba(220, 38, 38, 0.08); color: #B91C1C; }

    .activity-text strong { display: block; font-size: 0.84rem; font-weight: 500; color: #1C2536; }
    .activity-text span { font-size: 0.74rem; color: #808C9E; }

    .activity-amount { margin-left: auto; font-size: 0.85rem; font-weight: 600; }
    .activity-amount.in { color: #15803D; }
    .activity-amount.out { color: #B91C1C; }

    /* ========================================================
       TELLER FOCUS MODE - LUXURY GLASSMORPHISM STYLING
       ======================================================== */
    body.teller-focus-mode #appSidebar,
    body.teller-focus-mode .sidebar {
        display: none !important;
    }

    body.teller-focus-mode .topbar,
    body.teller-focus-mode header.topbar {
        display: none !important;
    }

    body.teller-focus-mode .main-wrapper {
        margin-left: 0 !important;
        width: 100% !important;
        min-height: 100vh !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        background: radial-gradient(circle at 50% 15%, rgba(59, 91, 165, 0.14) 0%, rgba(14, 24, 48, 0.04) 100%), #F4F7F6 !important;
        transition: all 0.3s ease;
    }

    body.teller-focus-mode .content-area {
        padding: 24px 16px !important;
        width: 100% !important;
        max-width: 620px !important;
        margin: 0 auto !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
    }

    .focus-card-wrapper {
        width: 100%;
        max-width: 560px;
        background: rgba(255, 255, 255, 0.88);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 28px;
        box-shadow: 0 25px 60px rgba(22, 35, 63, 0.14), inset 0 1px 0 rgba(255,255,255,0.7);
        padding: 36px 34px;
        position: relative;
        animation: focusCardFadeIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes focusCardFadeIn {
        from { opacity: 0; transform: scale(0.96) translateY(12px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }

    .focus-avatar-box {
        width: 72px;
        height: 72px;
        border-radius: 24px;
        background: linear-gradient(135deg, #16233F 0%, #2A3E63 100%);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.85rem;
        font-weight: 700;
        margin: 0 auto 16px;
        box-shadow: 0 12px 28px rgba(22, 35, 63, 0.25);
    }

    .balance-hero-box {
        background: linear-gradient(135deg, rgba(22, 163, 74, 0.08) 0%, rgba(20, 184, 166, 0.05) 100%);
        border: 1px solid rgba(22, 163, 74, 0.2);
        border-radius: 20px;
        padding: 20px;
        text-align: center;
        margin: 20px 0 24px;
    }

    .balance-hero-box .balance-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: #166534;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }

    .balance-hero-box .balance-val {
        font-size: 2rem;
        font-weight: 700;
        color: #15803D;
        line-height: 1.1;
    }

    /* ===== Buttons Focus Mode ===== */
    .btn-focus-setor {
        background: linear-gradient(135deg, #0F766E 0%, #14B8A6 100%);
        color: #fff;
        border: none;
        border-radius: 16px;
        padding: 14px 20px;
        font-weight: 600;
        font-size: 0.96rem;
        box-shadow: 0 10px 22px rgba(15, 118, 110, 0.28);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-focus-setor:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(15, 118, 110, 0.36);
        color: #fff;
    }

    .btn-focus-tarik {
        background: linear-gradient(135deg, #B45309 0%, #F59E0B 100%);
        color: #fff;
        border: none;
        border-radius: 16px;
        padding: 14px 20px;
        font-weight: 600;
        font-size: 0.96rem;
        box-shadow: 0 10px 22px rgba(180, 83, 9, 0.28);
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-focus-tarik:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 28px rgba(180, 83, 9, 0.36);
        color: #fff;
    }

    .btn-focus-cancel {
        background: rgba(22, 35, 63, 0.05);
        color: #475569;
        border: 1px solid rgba(22, 35, 63, 0.12);
        border-radius: 16px;
        padding: 12px 20px;
        font-weight: 500;
        font-size: 0.9rem;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .btn-focus-cancel:hover {
        background: rgba(22, 35, 63, 0.1);
        color: #1E293B;
    }

    /* ===== Form Inputs ===== */
    .focus-field-group {
        margin-bottom: 20px;
        text-align: left;
    }

    .focus-field-label {
        font-size: 0.83rem;
        font-weight: 600;
        color: #1E293B;
        margin-bottom: 8px;
        display: block;
    }

    .focus-field-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .focus-field-wrap i.prefix-icon {
        position: absolute;
        left: 16px;
        color: #94A3B8;
        font-size: 1.05rem;
    }

    .focus-field-wrap span.prefix-currency {
        position: absolute;
        left: 18px;
        color: #1E293B;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .focus-field-input {
        width: 100%;
        padding: 13px 18px 13px 48px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.95rem;
        background: #ffffff;
        border: 1.5px solid rgba(22, 35, 63, 0.14);
        border-radius: 14px;
        color: #1E293B;
        outline: none;
        transition: all 0.2s ease;
    }

    .focus-field-input:focus {
        border-color: #3B5BA5;
        box-shadow: 0 0 0 4px rgba(59, 91, 165, 0.14);
    }

    .focus-field-input[readonly] {
        background: #F1F5F9 !important;
        color: #475569 !important;
        cursor: not-allowed;
        border-color: #CBD5E1;
        font-weight: 600;
    }

    /* Quick Chips */
    .nominal-chip {
        display: inline-block;
        padding: 6px 12px;
        font-size: 0.78rem;
        font-weight: 500;
        color: #334155;
        background: #F1F5F9;
        border: 1px solid #E2E8F0;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.15s ease;
        user-select: none;
    }

    .nominal-chip:hover {
        background: #E2E8F0;
        color: #0F172A;
        transform: translateY(-1px);
    }

    .nominal-chip:active {
        transform: scale(0.96);
    }

    /* ===== QR Scanner Box (Persis Halaman Tarik Tunai) ===== */
    .scanner-viewport-box {
        position: relative;
        background: #090D16;
        border-radius: 20px;
        overflow: hidden;
        width: 100%;
        max-width: 360px;
        aspect-ratio: 1 / 1;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #qr-reader {
        width: 100% !important;
        height: 100% !important;
        border: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative;
    }

    #qr-reader video {
        width: 100% !important;
        height: 100% !important;
        border-radius: 18px !important;
        object-fit: cover !important;
        display: block !important;
        margin: 0 auto !important;
    }

    #qr-reader__scan_region {
        width: 100% !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
    }

    #qr-reader button, #qr-reader select, #qr-reader img {
        display: none !important;
    }

    .scanner-laser-line {
        position: absolute;
        width: 80%;
        height: 2px;
        background: linear-gradient(90deg, rgba(245,158,11,0) 0%, rgba(245,158,11,1) 50%, rgba(245,158,11,0) 100%);
        box-shadow: 0 0 12px #F59E0B;
        animation: scannerLaser 2s infinite ease-in-out;
        pointer-events: none;
        z-index: 10;
    }

    @keyframes scannerLaser {
        0% { top: 15%; opacity: 0.4; }
        50% { top: 85%; opacity: 1; }
        100% { top: 15%; opacity: 0.4; }
    }

    .scanner-corner {
        position: absolute;
        width: 28px;
        height: 28px;
        border-color: #ffffff;
        border-style: solid;
        pointer-events: none;
        z-index: 10;
    }

    .corner-tl { top: 24px; left: 24px; border-width: 3px 0 0 3px; border-top-left-radius: 6px; }
    .corner-tr { top: 24px; right: 24px; border-width: 3px 3px 0 0; border-top-right-radius: 6px; }
    .corner-bl { bottom: 24px; left: 24px; border-width: 0 0 3px 3px; border-bottom-left-radius: 6px; }
    .corner-br { bottom: 24px; right: 24px; border-width: 0 3px 3px 0; border-bottom-right-radius: 6px; }
</style>
@endpush
<!-- Synchronized: Bank Mini Teller Dashboard Complete View -->

@section('content')

    <!-- ========================================================
         VIEW 1: DEFAULT TELLER DASHBOARD
         ======================================================== -->
    <div id="dashboardDefaultView">
        
        <!-- Banner Sapaan + Aksi Tambah Kas Laci (Rapi & Tidak Bertumpuk) -->
        <div class="greeting-banner d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div style="position: relative; z-index: 2; max-width: 600px;">
                <h2>Halo, {{ Auth::user()->officer_name ?? Auth::user()->username ?? 'Petugas Teller' }} 👋</h2>
                <p class="mb-3">Loket kamu siap melayani transaksi hari ini. Jika uang kas fisik di laci menipis atau habis, gunakan tombol <strong>Tambah Kas Laci</strong>.</p>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="shift-chip"><i class="bi bi-clock-fill"></i> Shift Aktif · Modal Awal: Rp {{ number_format($saldoAwal ?? ($activeShift->opening_balance ?? 0), 0, ',', '.') }}</div>
                    <div class="shift-chip" style="background: rgba(16, 185, 129, 0.22); border-color: rgba(16, 185, 129, 0.4); color: #A7F3D0;">
                        <i class="bi bi-cash-stack"></i> Sisa Kas Fisik Laci: <strong class="text-white">Rp {{ number_format($saldoKas ?? 0, 0, ',', '.') }}</strong>
                    </div>
                </div>
            </div>
            <div class="d-flex flex-column align-items-start align-items-md-end gap-2" style="position: relative; z-index: 2;">
                <button type="button" class="btn btn-light fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2" style="color: #16233F; font-size: 0.88rem;" data-bs-toggle="modal" data-bs-target="#modalTopUpKas">
                    <i class="bi bi-plus-circle-fill text-success fs-5"></i> Tambah Kas Laci
                </button>
            </div>
        </div>

        @if(($saldoKas ?? 0) <= 50000)
            <div class="alert alert-warning border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4 py-3 px-4" style="background: #FFFBEB; color: #92400E; border-left: 5px solid #F59E0B !important;">
                <div class="d-flex align-items-center gap-3">
                    <i class="bi bi-exclamation-triangle-fill fs-4 text-warning"></i>
                    <div>
                        <strong class="d-block">Perhatian: Uang Kas Fisik di Laci Menipis / Habis (Rp {{ number_format($saldoKas ?? 0, 0, ',', '.') }})</strong>
                        <span class="small">Ambil uang tunai tambahan dari brankas dan masukkan ke sistem agar transaksi penarikan nasabah tidak terhambat.</span>
                    </div>
                </div>
                <button type="button" class="btn btn-warning btn-sm fw-bold rounded-pill px-3 py-2 text-white" style="background: #D97706; border: none;" data-bs-toggle="modal" data-bs-target="#modalTopUpKas">
                    <i class="bi bi-plus-lg me-1"></i> Masukkan Tambahan Kas Sekarang
                </button>
            </div>
        @endif

        <!-- Scanner QR Code (Persis Halaman Tarik Tunai - Tengah & Simetris) -->
        <div class="card mb-4" style="border-radius: 20px; border: 1px solid var(--border-soft); overflow: hidden; box-shadow: var(--shadow-card);">
            <div class="card-header text-white d-flex align-items-center justify-content-between" style="border-bottom: none; background: linear-gradient(135deg, #16233F 0%, #24355A 100%) !important; padding: 18px 24px;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-qr-code-scan fs-5 me-2"></i>
                    <div>
                        <strong class="d-block" style="font-size: 0.95rem;">Pemindai Kode QR Kartu Siswa</strong>
                        <small style="color: rgba(255, 255, 255, 0.8); font-size: 0.74rem;">Kamera aktif otomatis membaca kartu nasabah</small>
                    </div>
                </div>
                <span class="badge bg-white text-dark rounded-pill px-3 py-1 shadow-sm fw-medium" style="font-size: 0.78rem;">
                    <i class="bi bi-record-fill me-1 text-success"></i> Siap Memindai
                </span>
            </div>
            <div class="card-body p-4 bg-white text-center">
                <div class="scanner-viewport-box">
                    <div id="qr-reader"></div>

                    <!-- Overlay Laser & Corner Frame (Persis Halaman Tarik Tunai) -->
                    <div class="scanner-laser-line"></div>
                    <div class="scanner-corner corner-tl"></div>
                    <div class="scanner-corner corner-tr"></div>
                    <div class="scanner-corner corner-bl"></div>
                    <div class="scanner-corner corner-br"></div>
                </div>
                <div id="qr-reader-results" class="mt-3 text-muted small fw-semibold">
                    Arahkan Kode QR kartu tabungan siswa ke tengah kotak kamera
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Cepat Manual -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <a href="{{ route('teller.setoran.create') }}" class="quick-action-card">
                    <div class="quick-action-icon setor"><i class="bi bi-arrow-down-left"></i></div>
                    <div class="quick-action-text">
                        <strong>Setor Tunai Manual</strong>
                        <span>Input setoran jika kartu siswa tertinggal</span>
                    </div>
                    <div class="quick-action-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
            </div>
            <div class="col-12 col-md-6">
                <a href="{{ route('teller.penarikan.create') }}" class="quick-action-card">
                    <div class="quick-action-icon tarik"><i class="bi bi-arrow-up-right"></i></div>
                    <div class="quick-action-text">
                        <strong>Tarik Tunai Manual</strong>
                        <span>Input penarikan tabungan manual nasabah</span>
                    </div>
                    <div class="quick-action-arrow"><i class="bi bi-chevron-right"></i></div>
                </a>
            </div>
        </div>

        <!-- Ringkasan Statistik Loket Hari Ini -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="mini-stat">
                    <div class="mini-label">Total Setor Hari Ini</div>
                    <div class="mini-value green" id="statTotalSetor">Rp {{ number_format($totalSetoran ?? $totalSetor ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mini-stat">
                    <div class="mini-label">Total Tarik Hari Ini</div>
                    <div class="mini-value red" id="statTotalTarik">Rp {{ number_format($totalPenarikan ?? $totalTarik ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mini-stat">
                    <div class="mini-label">Jumlah Transaksi</div>
                    <div class="mini-value" id="statJumlahTrx">{{ $jumlahTransaksi ?? 0 }}</div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="mini-stat d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="mini-label mb-0">Saldo Kas Laci</div>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold text-primary" style="font-size: 0.74rem;" data-bs-toggle="modal" data-bs-target="#modalTopUpKas">
                            <i class="bi bi-plus-circle"></i> Tambah Kas
                        </button>
                    </div>
                    <div class="mini-value mt-1" id="statSaldoKas">Rp {{ number_format($saldoKas ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>

        <!-- Riwayat Transaksi Loket Ini -->
        <div class="card-solid p-4">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <p class="panel-title">Transaksi Loket Terakhir</p>
                    <p class="panel-subtitle">Aktivitas transaksi yang diproses loket ini</p>
                </div>
                <a href="{{ route('teller.report.closing') }}" class="text-decoration-none fw-semibold" style="font-size:0.82rem; color:#3B5BA5;">
                    <i class="bi bi-file-earmark-text me-1"></i> Tutup Kas Harian
                </a>
            </div>

            <div class="activity-list">
                @if(isset($latestTransactions) && count($latestTransactions) > 0)
                    @foreach($latestTransactions as $tx)
                        <div class="activity-item">
                            @if(strtolower($tx->transaction_type) == 'deposit')
                                <div class="activity-icon in"><i class="bi bi-arrow-down-left"></i></div>
                            @else
                                <div class="activity-icon out"><i class="bi bi-arrow-up-right"></i></div>
                            @endif
                            <div class="activity-text">
                                <strong>{{ $tx->saver->account_number ?? '1001-0004' }} — {{ $tx->saver->student_name ?? 'Siswa' }}</strong>
                                <span>{{ strtolower($tx->transaction_type) == 'deposit' ? 'Setoran tunai' : 'Penarikan tunai' }} · {{ $tx->created_at->format('H:i') }}</span>
                            </div>
                            @if(strtolower($tx->transaction_type) == 'deposit')
                                <div class="activity-amount in">+Rp {{ number_format($tx->amount, 0, ',', '.') }}</div>
                            @else
                                <div class="activity-amount out">-Rp {{ number_format($tx->amount, 0, ',', '.') }}</div>
                            @endif
                        </div>
                    @endforeach
                @else
                    <p class="text-muted small text-center py-3 mb-0">Belum ada transaksi di loket ini hari ini.</p>
                @endif
            </div>
        </div>

    </div>

    <!-- Modal Tambah Uang Kas Laci (Top-Up Kas Shift) -->
    <div class="modal fade" id="modalTopUpKas" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.22); overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <div style="width: 48px; height: 48px; border-radius: 14px; background: #ECFDF5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">
                            <i class="bi bi-cash-coin"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark m-0">Tambah Uang Kas Laci</h5>
                            <p class="text-muted small mb-0">Masukkan nominal uang tunai tambahan yang diambil ke dalam laci</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form action="{{ route('teller.shift.topup') }}" method="POST">
                    @csrf
                    <div class="modal-body px-4 py-3">
                        <div class="p-3 rounded-3 mb-3 border" style="background: #F8FAFC;">
                            <div class="d-flex justify-content-between align-items-center small mb-1">
                                <span class="text-muted">Modal Kas Laci Saat Ini:</span>
                                <span class="fw-bold text-dark">Rp {{ number_format($saldoAwal ?? ($activeShift->opening_balance ?? 0), 0, ',', '.') }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="text-muted">Sisa Uang Fisik di Laci:</span>
                                <span class="fw-bold text-success fs-6">Rp {{ number_format($saldoKas ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">Nominal Tambahan Uang Kas (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-dark">Rp</span>
                                <input type="text" name="amount" class="form-control rupiah-input fw-bold fs-5 text-success" placeholder="Contoh: 500.000" required autofocus>
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <span class="nominal-chip" onclick="this.closest('.mb-3').querySelector('input').value='200.000'">+Rp 200.000</span>
                                <span class="nominal-chip" onclick="this.closest('.mb-3').querySelector('input').value='500.000'">+Rp 500.000</span>
                                <span class="nominal-chip" onclick="this.closest('.mb-3').querySelector('input').value='1.000.000'">+Rp 1.000.000</span>
                                <span class="nominal-chip" onclick="this.closest('.mb-3').querySelector('input').value='2.000.000'">+Rp 2.000.000</span>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold small text-dark">Keterangan (Opsional)</label>
                            <input type="text" name="notes" class="form-control" placeholder="Contoh: Tambahan kas penarikan dari brankas">
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-1 gap-2">
                        <button type="button" class="btn btn-light rounded-pill px-4 flex-fill" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 flex-fill fw-bold" style="background: linear-gradient(135deg, #059669, #10B981); border: none;">
                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Tambahan Kas
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ========================================================
         VIEW 2: TELLER FOCUS MODE STAGE (CENTERED IN VIEWPORT)
         ======================================================== -->
    <div id="tellerFocusStage" class="d-none" style="width: 100%;">

        <!-- ----------------------------------------------------
             STAGE A: PROFILE CARD (TERPUSAT SETELAH SCAN QR)
             ---------------------------------------------------- -->
        <div class="focus-card-wrapper text-center mx-auto d-none" id="focusProfileCard">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.76rem;">
                    <i class="bi bi-shield-check me-1"></i> Nasabah Terverifikasi
                </span>
                <button type="button" class="btn btn-sm btn-link text-danger text-decoration-none fw-semibold p-0" id="btnTopCancelProfile">
                    <i class="bi bi-x-circle me-1"></i> Batal / Selesai
                </button>
            </div>

            <!-- Avatar Inisial Siswa -->
            <div class="focus-avatar-box" id="profileAvatar">A</div>
            
            <!-- Nama Lengkap Siswa -->
            <h3 class="fw-bold text-dark mb-1" id="profileStudentName">Nama Siswa</h3>
            <p class="text-muted small mb-3" id="profileStatus">Status Rekening: Aktif</p>

            <!-- Metadata Nasabah Chips -->
            <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                <span class="badge bg-white text-dark border px-3 py-2 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-credit-card-2-front text-primary me-1"></i> No. Rekening: <span id="profileAccountNumber">-</span>
                </span>
                <span class="badge bg-white text-dark border px-3 py-2 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-person-vcard text-teal me-1"></i> NIS: <span id="profileStudentId">-</span>
                </span>
                <span class="badge bg-white text-dark border px-3 py-2 rounded-pill fw-semibold shadow-sm">
                    <i class="bi bi-mortarboard text-secondary me-1"></i> <span id="profileGradeClass">-</span>
                </span>
            </div>

            <!-- Saldo Saat Ini Hero Card -->
            <div class="balance-hero-box">
                <div class="balance-label">Saldo Rekening Saat Ini</div>
                <div class="balance-val" id="profileBalance">Rp 0</div>
            </div>

            <!-- 3 Tombol Tindakan Sesuai Fase 1 & 2 -->
            <div class="d-flex flex-column gap-2 mt-4">
                <button type="button" class="btn-focus-setor w-100" id="btnGoToDeposit">
                    <i class="bi bi-arrow-down-circle-fill fs-5"></i> Setor Tunai
                </button>
                <button type="button" class="btn-focus-tarik w-100" id="btnGoToWithdrawal">
                    <i class="bi bi-arrow-up-circle-fill fs-5"></i> Tarik Tunai
                </button>
                <button type="button" class="btn-focus-cancel w-100 mt-1" id="btnCancelFocusMode">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Batal / Selesai
                </button>
            </div>

        </div>

        <!-- ----------------------------------------------------
             STAGE B: FORM SETOR TUNAI (DEPOSIT CARD)
             ---------------------------------------------------- -->
        <div class="focus-card-wrapper mx-auto d-none" id="focusDepositCard">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0 fw-semibold" id="btnBackFromDeposit">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Profil
                </button>
            </div>

            <!-- Header Setor Tunai dengan Ikon Hijau Panah ke Bawah -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width: 50px; height: 50px; border-radius: 16px; background: rgba(15, 118, 110, 0.12); color: #0F766E; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="bi bi-arrow-down-circle-fill text-success"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-0 d-flex align-items-center">
                        <i class="bi bi-arrow-down-circle-fill text-success me-2"></i> Setor Tunai
                    </h4>
                    <p class="text-muted small mb-0">Masukkan nominal setoran tabungan siswa.</p>
                </div>
            </div>

            <!-- Strip Identitas Singkat Siswa -->
            <div class="p-2 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                <div class="small text-truncate">
                    <strong class="d-block text-dark" id="depositStudentNameSummary">Nama Siswa</strong>
                    <span class="text-muted small" id="depositClassSummary">Kelas -</span>
                </div>
                <div class="text-end small">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">Saldo Saat Ini:</span>
                    <strong class="text-success" id="depositBalanceSummary">Rp 0</strong>
                </div>
            </div>

            <!-- Alert Pesan Error Setoran -->
            <div id="depositErrorAlert" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3" role="alert"></div>

            <form id="formDepositAjax" onsubmit="return false;">
                @csrf
                
                <!-- Field 1: Nomor Rekening Siswa / NIS (Readonly - TANPA tombol Scan QR) -->
                <div class="focus-field-group">
                    <label class="focus-field-label" for="deposit_account_number">Nomor Rekening Siswa / NIS</label>
                    <div class="focus-field-wrap">
                        <i class="bi bi-credit-card-2-front-fill prefix-icon"></i>
                        <input
                            type="text"
                            id="deposit_account_number"
                            name="account_number"
                            class="focus-field-input"
                            readonly
                        >
                    </div>
                </div>

                <!-- Field 2: Jumlah Setoran (Rp) -->
                <div class="focus-field-group">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="focus-field-label mb-0" for="deposit_amount">Jumlah Setoran (Rp)</label>
                        <small class="text-muted" style="font-size: 0.74rem;">Min. Rp 1.000</small>
                    </div>
                    <div class="focus-field-wrap">
                        <span class="prefix-currency">Rp</span>
                        <input
                            type="number"
                            id="deposit_amount"
                            name="amount"
                            class="focus-field-input"
                            placeholder="0"
                            min="1000"
                            step="500"
                            required
                        >
                    </div>

                    <!-- Quick Chips Nominal -->
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <button type="button" class="nominal-chip" onclick="setDepositAmount(10000)">+10.000</button>
                        <button type="button" class="nominal-chip" onclick="setDepositAmount(20000)">+20.000</button>
                        <button type="button" class="nominal-chip" onclick="setDepositAmount(50000)">+50.000</button>
                        <button type="button" class="nominal-chip" onclick="setDepositAmount(100000)">+100.000</button>
                        <button type="button" class="nominal-chip" onclick="setDepositAmount(200000)">+200.000</button>
                        <button type="button" class="nominal-chip" onclick="setDepositAmount(500000)">+500.000</button>
                    </div>
                </div>

                <!-- Action Button: Full-width "Proses Setoran" -->
                <button type="submit" class="btn-focus-setor w-100 mt-3" id="btnSubmitDeposit">
                    <i class="bi bi-check-circle-fill"></i> Proses Setoran
                </button>

                <!-- Cancel Button: Kembali ke Profil -->
                <button type="button" class="btn-focus-cancel w-100 mt-2" id="btnCancelDeposit">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Profil
                </button>
            </form>

        </div>

        <!-- ----------------------------------------------------
             STAGE C: FORM TARIK TUNAI (WITHDRAWAL CARD)
             ---------------------------------------------------- -->
        <div class="focus-card-wrapper mx-auto d-none" id="focusWithdrawalCard">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button type="button" class="btn btn-sm btn-link text-muted text-decoration-none p-0 fw-semibold" id="btnBackFromWithdrawal">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Profil
                </button>
            </div>

            <!-- Header Tarik Tunai dengan Ikon Merah Panah ke Atas -->
            <div class="d-flex align-items-center gap-3 mb-3">
                <div style="width: 50px; height: 50px; border-radius: 16px; background: rgba(220, 38, 38, 0.12); color: #DC2626; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="bi bi-arrow-up-circle-fill text-danger"></i>
                </div>
                <div>
                    <h4 class="fw-bold text-dark mb-0 d-flex align-items-center">
                        <i class="bi bi-arrow-up-circle-fill text-danger me-2"></i> Tarik Tunai
                    </h4>
                    <p class="text-muted small mb-0">Proses penarikan uang tabungan siswa.</p>
                </div>
            </div>

            <!-- Strip Identitas Singkat Siswa -->
            <div class="p-2 px-3 rounded-3 mb-3 d-flex align-items-center justify-content-between" style="background: #F8FAFC; border: 1px solid #E2E8F0;">
                <div class="small text-truncate">
                    <strong class="d-block text-dark" id="withdrawalStudentNameSummary">Nama Siswa</strong>
                    <span class="text-muted small" id="withdrawalClassSummary">Kelas -</span>
                </div>
                <div class="text-end small">
                    <span class="text-muted d-block" style="font-size: 0.7rem;">Saldo Tersedia:</span>
                    <strong class="text-danger" id="withdrawalBalanceSummary">Rp 0</strong>
                </div>
            </div>

            <!-- Alert Pesan Error Penarikan -->
            <div id="withdrawalErrorAlert" class="alert alert-danger py-2 px-3 small rounded-3 d-none mb-3" role="alert"></div>

            <form id="formWithdrawalAjax" onsubmit="return false;">
                @csrf

                <!-- Field 1: Nomor Rekening Siswa / NIS (Readonly - TANPA tombol Scan QR) -->
                <div class="focus-field-group">
                    <label class="focus-field-label" for="withdrawal_account_number">Nomor Rekening Siswa / NIS</label>
                    <div class="focus-field-wrap">
                        <i class="bi bi-credit-card-2-front-fill prefix-icon"></i>
                        <input
                            type="text"
                            id="withdrawal_account_number"
                            name="account_number"
                            class="focus-field-input"
                            readonly
                        >
                    </div>
                </div>

                <!-- Field 2: Jumlah Penarikan (Rp) -->
                <div class="focus-field-group">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="focus-field-label mb-0" for="withdrawal_amount">Jumlah Penarikan (Rp)</label>
                        <small class="text-muted" style="font-size: 0.74rem;">Min. Rp 1.000</small>
                    </div>
                    <div class="focus-field-wrap">
                        <span class="prefix-currency">Rp</span>
                        <input
                            type="number"
                            id="withdrawal_amount"
                            name="amount"
                            class="focus-field-input"
                            placeholder="0"
                            min="1000"
                            step="500"
                            required
                        >
                    </div>

                    <!-- Quick Chips Nominal -->
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <button type="button" class="nominal-chip" onclick="setWithdrawalAmount(10000)">10.000</button>
                        <button type="button" class="nominal-chip" onclick="setWithdrawalAmount(20000)">20.000</button>
                        <button type="button" class="nominal-chip" onclick="setWithdrawalAmount(50000)">50.000</button>
                        <button type="button" class="nominal-chip" onclick="setWithdrawalAmount(100000)">100.000</button>
                        <button type="button" class="nominal-chip" onclick="setWithdrawalAmount(200000)">200.000</button>
                    </div>
                </div>

                <!-- Field 3: PIN Keamanan Siswa (6 Digit) -->
                <div class="focus-field-group">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="focus-field-label mb-0" for="withdrawal_pin">PIN Keamanan Nasabah (6 Digit)</label>
                        <small class="text-muted"><i class="bi bi-lock-fill"></i> Terenkripsi</small>
                    </div>
                    <div class="focus-field-wrap">
                        <i class="bi bi-key-fill prefix-icon text-warning"></i>
                        <input
                            type="password"
                            id="withdrawal_pin"
                            name="security_pin"
                            class="focus-field-input fw-bold"
                            style="letter-spacing: 6px;"
                            maxlength="6"
                            placeholder="••••••"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            required
                        >
                    </div>
                    <small class="text-muted" style="font-size: 0.73rem;">Silakan arahkan siswa untuk mengetik 6 digit PIN rahasia.</small>
                </div>

                <!-- Action Button: Full-width "Proses Penarikan" -->
                <button type="submit" class="btn-focus-tarik w-100 mt-2" id="btnSubmitWithdrawal">
                    <i class="bi bi-check-circle-fill"></i> Proses Penarikan
                </button>

                <!-- Cancel Button: Kembali ke Profil -->
                <button type="button" class="btn-focus-cancel w-100 mt-2" id="btnCancelWithdrawal">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Profil
                </button>
            </form>

        </div>

    </div>

    <!-- ========================================================
         MODAL STRUK TRANSAKSI SUKSES (RECEIPT MODAL)
         ======================================================== -->
    <div class="modal fade" id="modalFocusReceipt" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.25);">
                <div class="modal-body p-4 text-center">
                    <div class="mb-2"><i class="bi bi-check-circle-fill fs-1 text-success"></i></div>
                    <h5 class="fw-bold mb-0 text-dark">Transaksi Berhasil Diproses!</h5>
                    <small class="text-muted d-block mb-3">BANK MINI SMKN 1 BANTUL</small>
                    <hr style="border-top: 1px dashed #94A3B8;">

                    <div class="text-start small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">No. Transaksi:</span>
                            <span class="fw-semibold" id="receiptTxId">#TRX-0</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Waktu:</span>
                            <span id="receiptTxDate">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Teller:</span>
                            <span>{{ Auth::user()->officer_name ?? Auth::user()->username ?? 'Teller' }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">No. Rekening:</span>
                            <span class="fw-bold" id="receiptAccountNum">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Nama Nasabah:</span>
                            <span class="fw-bold" id="receiptStudentName">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Jenis:</span>
                            <span class="fw-semibold" id="receiptType">-</span>
                        </div>

                        <div class="p-2 rounded-3 bg-light text-center mb-2">
                            <small class="text-muted d-block">Jumlah Nominal:</small>
                            <span class="fs-5 fw-bold" id="receiptAmount">Rp 0</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Saldo Akhir:</span>
                            <span class="fw-bold text-success" id="receiptFinalBalance">Rp 0</span>
                        </div>
                    </div>

                    <hr style="border-top: 1px dashed #94A3B8;">
                    
                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-outline-secondary rounded-pill w-100" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak Struk
                        </button>
                        <button type="button" class="btn btn-primary rounded-pill w-100 fw-bold" id="btnCloseReceiptAndNext">
                            <i class="bi bi-qr-code-scan me-1"></i> Selesai & Pindai Berikutnya
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements - Views
    const defaultView          = document.getElementById('dashboardDefaultView');
    const focusStage           = document.getElementById('tellerFocusStage');
    const profileCard          = document.getElementById('focusProfileCard');
    const depositCard          = document.getElementById('focusDepositCard');
    const withdrawalCard       = document.getElementById('focusWithdrawalCard');
    
    // DOM Elements - Profile Card Elements
    const profileAvatar        = document.getElementById('profileAvatar');
    const profileStudentName   = document.getElementById('profileStudentName');
    const profileAccountNumber = document.getElementById('profileAccountNumber');
    const profileStudentId     = document.getElementById('profileStudentId');
    const profileGradeClass    = document.getElementById('profileGradeClass');
    const profileBalance       = document.getElementById('profileBalance');
    const profileStatus        = document.getElementById('profileStatus');

    // DOM Elements - Buttons in Profile Card
    const btnGoToDeposit       = document.getElementById('btnGoToDeposit');
    const btnGoToWithdrawal    = document.getElementById('btnGoToWithdrawal');
    const btnCancelFocusMode   = document.getElementById('btnCancelFocusMode');
    const btnTopCancelProfile  = document.getElementById('btnTopCancelProfile');

    // DOM Elements - Deposit Form
    const depositAccountNumber = document.getElementById('deposit_account_number');
    const depositAmount        = document.getElementById('deposit_amount');
    const depositStudentNameSm = document.getElementById('depositStudentNameSummary');
    const depositClassSm       = document.getElementById('depositClassSummary');
    const depositBalanceSm     = document.getElementById('depositBalanceSummary');
    const btnBackFromDeposit   = document.getElementById('btnBackFromDeposit');
    const btnCancelDeposit     = document.getElementById('btnCancelDeposit');
    const formDepositAjax      = document.getElementById('formDepositAjax');
    const btnSubmitDeposit     = document.getElementById('btnSubmitDeposit');
    const depositErrorAlert    = document.getElementById('depositErrorAlert');

    // DOM Elements - Withdrawal Form
    const withdrawalAccountNum = document.getElementById('withdrawal_account_number');
    const withdrawalAmount     = document.getElementById('withdrawal_amount');
    const withdrawalPin        = document.getElementById('withdrawal_pin');
    const withdrawalStudentSm  = document.getElementById('withdrawalStudentNameSummary');
    const withdrawalClassSm    = document.getElementById('withdrawalClassSummary');
    const withdrawalBalanceSm  = document.getElementById('withdrawalBalanceSummary');
    const btnBackFromWithdrawal= document.getElementById('btnBackFromWithdrawal');
    const btnCancelWithdrawal  = document.getElementById('btnCancelWithdrawal');
    const formWithdrawalAjax   = document.getElementById('formWithdrawalAjax');
    const btnSubmitWithdrawal  = document.getElementById('btnSubmitWithdrawal');
    const withdrawalErrorAlert = document.getElementById('withdrawalErrorAlert');

    // Receipt Modal Elements
    const modalReceiptEl       = document.getElementById('modalFocusReceipt');
    const modalReceipt         = new bootstrap.Modal(modalReceiptEl);
    const btnCloseReceiptNext  = document.getElementById('btnCloseReceiptAndNext');

    // Global Active Student Object & Scan Lock
    let activeStudent = null;
    let html5QrCode = null;
    let isProcessing = false;

    function pauseScanner() {
        isProcessing = true;
        if (html5QrCode) {
            try {
                html5QrCode.pause(true);
            } catch (err) {
                // Ignore if pause is not supported
            }
        }
    }

    function resumeScanner() {
        isProcessing = false;
        if (html5QrCode) {
            try {
                html5QrCode.resume();
            } catch (err) {
                // Ignore if resume is not supported
            }
        }
        const qrResultEl = document.getElementById('qr-reader-results');
        if (qrResultEl) {
            qrResultEl.innerText = "Arahkan Kode QR kartu tabungan siswa ke tengah kotak kamera";
        }
    }

    // Web Audio Sound Chimes
    function playAudio(type = 'success') {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            
            if (type === 'success') {
                osc.type = 'sine';
                osc.frequency.setValueAtTime(523.25, ctx.currentTime);
                osc.frequency.setValueAtTime(659.25, ctx.currentTime + 0.08);
                osc.frequency.setValueAtTime(783.99, ctx.currentTime + 0.16);
                gain.gain.setValueAtTime(0.12, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.35);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.35);
            } else {
                osc.type = 'square';
                osc.frequency.setValueAtTime(300, ctx.currentTime);
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.2);
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.start();
                osc.stop(ctx.currentTime + 0.2);
            }
        } catch (e) {
            // Audio blocked or not supported
        }
    }

    // Parse Scanned Data
    function parseIdentifier(scannedText) {
        let text = (scannedText || '').trim();
        if (text.startsWith('{') && text.endsWith('}')) {
            try {
                const parsed = JSON.parse(text);
                return parsed.account_number || parsed.student_id || parsed.nis || parsed.q || text;
            } catch (e) {
                return text;
            }
        }
        return text;
    }

    // ========================================================
    // PHASE 1: TRANSITION STATE LOGIC (FOCUS MODE)
    // ========================================================
    async function handleQrDetected(rawText) {
        const identifier = parseIdentifier(rawText);
        if (!identifier) {
            resumeScanner();
            return;
        }

        playAudio('success');
        const qrResultEl = document.getElementById('qr-reader-results');
        if (qrResultEl) {
            qrResultEl.innerText = "Berhasil membaca QR: " + identifier + ". Memuat data nasabah...";
        }

        try {
            const res = await fetch(`{{ route('teller.search-saver') }}?q=${encodeURIComponent(identifier)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await res.json();

            if (res.ok && result.success && result.data) {
                activeStudent = result.data;
                enterFocusMode(activeStudent);
            } else {
                alert(result.message || 'Data nasabah tidak ditemukan di sistem.');
                resumeScanner();
            }
        } catch (err) {
            console.error(err);
            alert('Gagal menghubungi server. Pastikan jaringan terhubung.');
            resumeScanner();
        }
    }

    function enterFocusMode(student) {
        // 1. Sembunyikan Scanner & Dashboard Biasa
        defaultView.classList.add('d-none');

        // 2. Terapkan Mode Fokus (Sembunyikan Sidebar & Header)
        document.body.classList.add('teller-focus-mode');

        // 3. Populasi Data ke Profile Card
        profileAvatar.textContent = (student.student_name || 'S').charAt(0).toUpperCase();
        profileStudentName.textContent = student.student_name || student.name || 'Siswa';
        profileAccountNumber.textContent = student.account_number || '-';
        profileStudentId.textContent = student.student_id || student.nis || '-';
        profileGradeClass.textContent = student.grade_class || 'Siswa';
        profileBalance.textContent = student.formatted_saldo || ('Rp ' + Number(student.saldo || 0).toLocaleString('id-ID'));
        profileStatus.textContent = student.is_member_only ? 'Status: Anggota Baru (Aktivasi Setoran Awal)' : 'Status Rekening: Aktif';

        // 4. Tampilkan Stage Fokus & Profile Card via .d-none
        focusStage.classList.remove('d-none');
        profileCard.classList.remove('d-none');
        depositCard.classList.add('d-none');
        withdrawalCard.classList.add('d-none');
    }

    function exitFocusMode(e) {
        if (e) e.preventDefault();

        // 1. Hapus class Mode Fokus (Kembalikan Sidebar & Header)
        document.body.classList.remove('teller-focus-mode');

        // 2. Reset State & Sembunyikan Stage Fokus
        focusStage.classList.add('d-none');
        profileCard.classList.add('d-none');
        depositCard.classList.add('d-none');
        withdrawalCard.classList.add('d-none');
        activeStudent = null;

        // 3. Tampilkan kembali Dashboard Default
        defaultView.classList.remove('d-none');

        // 4. Buka kunci scanner dan lanjutkan pemindaian
        resumeScanner();
    }

    // Event Batal dari Profile Card (Batal / Selesai)
    btnCancelFocusMode.addEventListener('click', function(e) {
        e.preventDefault();
        exitFocusMode(e);
    });

    if (btnTopCancelProfile) {
        btnTopCancelProfile.addEventListener('click', function(e) {
            e.preventDefault();
            exitFocusMode(e);
        });
    }

    // ========================================================
    // PHASE 2: FORM TRANSITIONS (DEPOSIT & WITHDRAWAL)
    // ========================================================

    // Switch ke Formulir Setor Tunai
    btnGoToDeposit.addEventListener('click', function (e) {
        e.preventDefault(); // CRITICAL: Mencegah reload / looping bug
        if (!activeStudent) return;

        // 1. Sembunyikan Profile Card & Withdrawal Card, Tampilkan Deposit Card
        profileCard.classList.add('d-none');
        depositCard.classList.remove('d-none');
        withdrawalCard.classList.add('d-none');

        // 2. Populasi Form Setor Tunai
        depositAccountNumber.value = activeStudent.account_number || activeStudent.student_id;
        depositStudentNameSm.textContent = activeStudent.student_name || activeStudent.name;
        depositClassSm.textContent = `NIS: ${activeStudent.student_id || '-'} · ${activeStudent.grade_class || '-'}`;
        depositBalanceSm.textContent = activeStudent.formatted_saldo || ('Rp ' + Number(activeStudent.saldo || 0).toLocaleString('id-ID'));
        
        depositAmount.value = '';
        depositErrorAlert.classList.add('d-none');

        // 3. Autofocus langsung ke Nominal Setoran
        setTimeout(() => {
            depositAmount.focus();
            depositAmount.select();
        }, 80);
    });

    // Switch ke Formulir Tarik Tunai
    btnGoToWithdrawal.addEventListener('click', function (e) {
        e.preventDefault(); // CRITICAL: Mencegah reload / looping bug
        if (!activeStudent) return;

        // 1. Sembunyikan Profile Card & Deposit Card, Tampilkan Withdrawal Card
        profileCard.classList.add('d-none');
        depositCard.classList.add('d-none');
        withdrawalCard.classList.remove('d-none');

        // 2. Populasi Form Tarik Tunai
        withdrawalAccountNum.value = activeStudent.account_number || activeStudent.student_id;
        withdrawalStudentSm.textContent = activeStudent.student_name || activeStudent.name;
        withdrawalClassSm.textContent = `NIS: ${activeStudent.student_id || '-'} · ${activeStudent.grade_class || '-'}`;
        withdrawalBalanceSm.textContent = activeStudent.formatted_saldo || ('Rp ' + Number(activeStudent.saldo || 0).toLocaleString('id-ID'));
        
        withdrawalAmount.value = '';
        withdrawalPin.value = '';
        withdrawalErrorAlert.classList.add('d-none');

        // 3. Autofocus langsung ke Nominal Penarikan
        setTimeout(() => {
            withdrawalAmount.focus();
            withdrawalAmount.select();
        }, 80);
    });

    // Navigasi Kembali ke Profil Siswa (Kembali ke Profil)
    function backToProfile(e) {
        if (e) e.preventDefault(); // CRITICAL
        depositCard.classList.add('d-none');
        withdrawalCard.classList.add('d-none');
        profileCard.classList.remove('d-none');
    }

    btnBackFromDeposit.addEventListener('click', backToProfile);
    btnCancelDeposit.addEventListener('click', backToProfile);
    btnBackFromWithdrawal.addEventListener('click', backToProfile);
    btnCancelWithdrawal.addEventListener('click', backToProfile);

    // Helper Fungsi Cepat Nominal Chip
    window.setDepositAmount = function (nom) {
        depositAmount.value = nom;
        depositAmount.focus();
    };

    window.setWithdrawalAmount = function (nom) {
        withdrawalAmount.value = nom;
        withdrawalAmount.focus();
    };

    // ========================================================
    // PHASE 3: AJAX SUBMISSION & RESET MECHANISM
    // ========================================================

    // Submit Setoran Tunai via AJAX
    formDepositAjax.addEventListener('submit', async function (e) {
        e.preventDefault();

        const amountVal = parseInt(depositAmount.value);
        if (!amountVal || amountVal < 1000) {
            depositErrorAlert.textContent = 'Nominal setoran minimal adalah Rp 1.000.';
            depositErrorAlert.classList.remove('d-none');
            depositAmount.focus();
            return;
        }

        btnSubmitDeposit.disabled = true;
        btnSubmitDeposit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses Setoran...';
        depositErrorAlert.classList.add('d-none');

        try {
            const response = await fetch("{{ route('teller.deposit') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    account_number: depositAccountNumber.value,
                    amount: amountVal
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                playAudio('success');
                showReceiptModal(result.data, 'Setoran Tunai');
            } else {
                throw new Error(result.message || 'Gagal memproses setoran.');
            }

        } catch (err) {
            playAudio('error');
            depositErrorAlert.textContent = err.message || 'Terjadi kesalahan sistem.';
            depositErrorAlert.classList.remove('d-none');
        } finally {
            btnSubmitDeposit.disabled = false;
            btnSubmitDeposit.innerHTML = '<i class="bi bi-check-circle-fill"></i> Proses Setoran';
        }
    });

    // Submit Penarikan Tunai via AJAX
    formWithdrawalAjax.addEventListener('submit', async function (e) {
        e.preventDefault();

        const amountVal = parseInt(withdrawalAmount.value);
        if (!amountVal || amountVal < 1000) {
            withdrawalErrorAlert.textContent = 'Nominal penarikan minimal adalah Rp 1.000.';
            withdrawalErrorAlert.classList.remove('d-none');
            withdrawalAmount.focus();
            return;
        }

        if (withdrawalPin.value.length !== 6) {
            withdrawalErrorAlert.textContent = 'PIN keamanan nasabah harus terdiri dari 6 digit angka.';
            withdrawalErrorAlert.classList.remove('d-none');
            withdrawalPin.focus();
            return;
        }

        btnSubmitWithdrawal.disabled = true;
        btnSubmitWithdrawal.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses Penarikan...';
        withdrawalErrorAlert.classList.add('d-none');

        try {
            const response = await fetch("{{ route('teller.withdrawal') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    account_number: withdrawalAccountNum.value,
                    amount: amountVal,
                    security_pin: withdrawalPin.value
                })
            });

            const result = await response.json();

            if (response.ok && result.success) {
                playAudio('success');
                showReceiptModal(result.data, 'Penarikan Tunai');
            } else {
                throw new Error(result.message || 'Gagal memproses penarikan.');
            }

        } catch (err) {
            playAudio('error');
            withdrawalErrorAlert.textContent = err.message || 'Terjadi kesalahan sistem.';
            withdrawalErrorAlert.classList.remove('d-none');
        } finally {
            btnSubmitWithdrawal.disabled = false;
            btnSubmitWithdrawal.innerHTML = '<i class="bi bi-check-circle-fill"></i> Proses Penarikan';
        }
    });

    // Tampilkan Modal Struk Sukses
    function showReceiptModal(txData, typeLabel) {
        document.getElementById('receiptTxId').textContent = '#TRX-' + (txData.id || txData.transaction_id || '0');
        document.getElementById('receiptTxDate').textContent = txData.date || new Date().toLocaleString('id-ID');
        document.getElementById('receiptAccountNum').textContent = txData.account_number;
        document.getElementById('receiptStudentName').textContent = txData.student_name;
        document.getElementById('receiptType').textContent = typeLabel;
        
        const isDeposit = typeLabel.includes('Setoran');
        const amountEl = document.getElementById('receiptAmount');
        amountEl.textContent = (isDeposit ? '+ ' : '- ') + 'Rp ' + Number(txData.amount).toLocaleString('id-ID');
        amountEl.className = 'fs-5 fw-bold ' + (isDeposit ? 'text-success' : 'text-danger');
        
        document.getElementById('receiptFinalBalance').textContent = 'Rp ' + Number(txData.current_balance).toLocaleString('id-ID');

        modalReceipt.show();
    }

    // Tutup Struk dan Kembali ke Default Camera Scanner State
    btnCloseReceiptNext.addEventListener('click', function (e) {
        e.preventDefault();
        modalReceipt.hide();
        exitFocusMode(e);
    });

    // ========================================================
    // SCANNER INITIALIZATION (Html5Qrcode - Kamera Simetris di Tengah)
    // ========================================================
    function onScanSuccess(decodedText, decodedResult) {
        // GATING & LOCK: Cegah loop dan request flood puluhan kali per detik
        if (isProcessing) return;
        pauseScanner();
        handleQrDetected(decodedText);
    }

    function onScanFailure(error) {
        // Continuous scan loop
    }

    try {
        html5QrCode = new Html5Qrcode("qr-reader");
        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 15,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            },
            onScanSuccess,
            onScanFailure
        ).catch(err => {
            console.warn("Kamera otomatis belum aktif atau izin ditolak:", err);
            const qrResultEl = document.getElementById('qr-reader-results');
            if (qrResultEl) {
                qrResultEl.innerHTML = '<span class="text-muted"><i class="bi bi-info-circle me-1"></i> Klik untuk mengizinkan akses kamera pemindai QR</span>';
            }
        });
    } catch (e) {
        console.error("Gagal inisialisasi Html5Qrcode:", e);
    }

    // Cek jika ada parameter ?account_number= di URL (misal redirect lama), bersihkan agar tidak loop
    const urlParams = new URLSearchParams(window.location.search);
    const initialAcc = urlParams.get('account_number');
    if (initialAcc) {
        if (window.history.replaceState) {
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        pauseScanner();
        handleQrDetected(initialAcc);
    }
});
</script>
@endpush
<!-- Synchronized: Bank Mini Teller Dashboard Complete View -->
