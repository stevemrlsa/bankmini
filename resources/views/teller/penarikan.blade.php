@extends('layouts.app')

@section('title', 'Penarikan Tunai — Bank Mini')
@section('header_title', 'Penarikan Tunai')
@section('page-title', 'Penarikan Tunai')
@section('page-subtitle', 'Proses pengambilan saldo nasabah')

@push('styles')
<style>
    .form-stage {
        display: flex;
        justify-content: center;
        padding-top: 8px;
    }

    .transaction-card {
        width: 100%;
        max-width: 540px;
        background: rgba(255, 255, 255, 0.82);
        backdrop-filter: blur(16px) saturate(160%);
        -webkit-backdrop-filter: blur(16px) saturate(160%);
        border: 1px solid rgba(255, 255, 255, 0.6);
        border-radius: 26px;
        box-shadow: 0 20px 46px rgba(22, 35, 63, 0.1), inset 0 1px 0 rgba(255,255,255,0.5);
        padding: 34px 34px 30px;
    }

    .transaction-icon-badge {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: linear-gradient(135deg, #B45309, #F59E0B);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 1.4rem;
        margin-bottom: 16px;
        box-shadow: 0 10px 22px rgba(180, 83, 9, 0.28);
    }

    .transaction-card h2 {
        font-size: 1.18rem;
        font-weight: 600;
        color: #16233F;
        margin-bottom: 4px;
    }

    .transaction-card p.desc {
        font-size: 0.83rem;
        color: #808C9E;
        margin-bottom: 22px;
    }

    /* ===== Scan Button in Label ===== */
    .btn-scan-action-amber {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 12px;
        font-size: 0.76rem;
        font-weight: 600;
        color: #B45309;
        background: rgba(245, 158, 11, 0.12);
        border: 1px solid rgba(245, 158, 11, 0.25);
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .btn-scan-action-amber:hover {
        background: #B45309;
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(180, 83, 9, 0.25);
        transform: translateY(-1px);
    }

    /* ===== Alerts ===== */
    .alert-success-glass {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: rgba(22, 163, 74, 0.1);
        border: 1px solid rgba(22, 163, 74, 0.18);
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 22px;
    }

    .alert-success-glass .alert-icon {
        width: 32px; height: 32px; border-radius: 10px;
        background: #15803D; color: #fff; display: flex;
        align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;
    }

    .alert-success-glass strong { display: block; font-size: 0.85rem; color: #14532D; margin-bottom: 2px; }
    .alert-success-glass span { font-size: 0.78rem; color: #166534; }

    .alert-danger-glass {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        background: rgba(220, 38, 38, 0.08);
        border: 1px solid rgba(220, 38, 38, 0.16);
        border-radius: 16px;
        padding: 14px 16px;
        margin-bottom: 22px;
    }

    .alert-danger-glass .alert-icon {
        width: 32px; height: 32px; border-radius: 10px;
        background: #B91C1C; color: #fff; display: flex;
        align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;
    }

    .alert-danger-glass strong { display: block; font-size: 0.85rem; color: #7F1D1D; margin-bottom: 2px; }
    .alert-danger-glass span { font-size: 0.78rem; color: #991B1B; }

    /* ===== Field ===== */
    .field-group { margin-bottom: 18px; }

    .field-label {
        font-size: 0.82rem;
        font-weight: 500;
        color: #1C2536;
        margin-bottom: 7px;
        display: block;
    }

    .field-wrap { position: relative; display: flex; align-items: center; }

    .field-wrap i {
        position: absolute;
        left: 16px;
        color: #808C9E;
        font-size: 0.95rem;
    }

    .field-wrap .field-prefix {
        position: absolute;
        left: 16px;
        color: #1C2536;
        font-weight: 500;
        font-size: 0.88rem;
    }

    .field-input {
        width: 100%;
        padding: 12px 16px 12px 44px;
        font-family: 'Poppins', sans-serif;
        font-size: 0.9rem;
        background: rgba(255, 255, 255, 0.85);
        border: 1px solid rgba(22, 35, 63, 0.1);
        border-radius: 13px;
        color: #1C2536;
        outline: none;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
    }

    .field-input.with-prefix { padding-left: 40px; }

    .field-input::placeholder { color: rgba(128, 140, 158, 0.75); font-weight: 300; }

    .field-input:focus {
        background: #ffffff;
        border-color: #F59E0B;
        box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.14);
    }

    .field-input[readonly] {
        background: #F1F5F9 !important;
        cursor: not-allowed;
        color: #334155;
        border-color: #CBD5E1;
    }

    .field-helper { font-size: 0.74rem; color: #A2AABB; margin-top: 6px; }

    /* ===== Info nasabah preview card ===== */
    .saver-preview-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: linear-gradient(135deg, rgba(180, 83, 9, 0.08) 0%, rgba(245, 158, 11, 0.04) 100%);
        border: 1px solid rgba(180, 83, 9, 0.2);
        border-radius: 18px;
        padding: 14px 16px;
        margin-bottom: 20px;
        box-shadow: 0 8px 20px rgba(180, 83, 9, 0.06);
        animation: previewSlideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }

    @keyframes previewSlideIn {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .saver-preview-card .avatar-box {
        width: 44px;
        height: 44px;
        border-radius: 13px;
        background: linear-gradient(135deg, #B45309, #F59E0B);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        font-weight: 600;
        flex-shrink: 0;
        box-shadow: 0 4px 10px rgba(180, 83, 9, 0.25);
    }

    .saver-preview-card .cust-info {
        flex: 1;
        min-width: 0;
    }

    .saver-preview-card .cust-info strong {
        display: block;
        font-size: 0.92rem;
        color: #0F172A;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .saver-preview-card .cust-info span {
        font-size: 0.76rem;
        color: #475569;
    }

    .saver-preview-card .cust-balance {
        text-align: right;
        flex-shrink: 0;
    }

    .saver-preview-card .cust-balance strong {
        display: block;
        font-size: 0.96rem;
        color: #B45309;
        font-weight: 700;
    }

    .saver-preview-card .cust-balance span {
        font-size: 0.7rem;
        color: #64748B;
    }

    /* ===== Tombol Submit ===== */
    .btn-submit-amber {
        width: 100%;
        border: none;
        padding: 13px;
        border-radius: 13px;
        background: linear-gradient(135deg, #B45309 0%, #92400E 100%);
        color: #fff;
        font-family: 'Poppins', sans-serif;
        font-weight: 500;
        font-size: 0.92rem;
        box-shadow: 0 12px 24px rgba(180, 83, 9, 0.26);
        transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
    }

    .btn-submit-amber:hover {
        transform: translateY(-2px);
        box-shadow: 0 16px 30px rgba(180, 83, 9, 0.32);
        filter: brightness(1.05);
        color: #fff;
    }

    /* ===== QR Scanner Modal Styles (Centered & Symmetrical) ===== */
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

    #qr-scanner-viewport {
        width: 100% !important;
        height: 100% !important;
        border: none !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        position: relative;
    }

    #qr-scanner-viewport video {
        width: 100% !important;
        height: 100% !important;
        border-radius: 18px !important;
        object-fit: cover !important;
        display: block !important;
        margin: 0 auto !important;
    }

    #qr-scanner-viewport__scan_region {
        width: 100% !important;
        height: 100% !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
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
</style>
@endpush

@section('content')

    <div class="form-stage">
        <div class="transaction-card">

            <div class="d-flex justify-content-between align-items-start mb-2">
                <div class="transaction-icon-badge mb-0">
                    <i class="bi bi-arrow-up-circle-fill"></i>
                </div>
                <div class="text-end">
                    <div class="small text-muted" style="font-size: 0.74rem;">Uang Kas Fisik di Laci:</div>
                    <div class="fw-bold {{ ($uangFisikDiLaci ?? 0) <= 50000 ? 'text-danger' : 'text-success' }}" style="font-size: 0.95rem;">
                        Rp {{ number_format($uangFisikDiLaci ?? 0, 0, ',', '.') }}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-0 mt-1 fw-semibold" style="font-size: 0.72rem;" data-bs-toggle="modal" data-bs-target="#modalTopUpKas">
                        <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kas Laci
                    </button>
                </div>
            </div>
            <h2 class="mt-3">Penarikan Tunai</h2>
            <p class="desc">Scan QR Code nasabah dan verifikasi PIN sebelum penyerahan uang.</p>

            @if(session('success'))
            <div class="alert-success-glass">
                <div class="alert-icon"><i class="bi bi-check-lg"></i></div>
                <div>
                    <strong>Penarikan berhasil diproses</strong>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="alert alert-danger rounded-4 py-3 px-3 mb-3 small d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>{{ session('error') }}</div>
            </div>
            @endif

            @if($errors->has('security_pin') || $errors->has('pin'))
            <div class="alert-danger-glass">
                <div class="alert-icon"><i class="bi bi-x-lg"></i></div>
                <div>
                    <strong>PIN tidak sesuai</strong>
                    <span>{{ $errors->first('security_pin') ?: $errors->first('pin') }}</span>
                </div>
            </div>
            @endif

            <!-- Banner Notifikasi Jika Pencairan Berdasarkan Janjian Penarikan Dana Besar -->
            @if(isset($prefilledRequest) && $prefilledRequest)
            <div id="bannerJanjianPenarikan" class="alert alert-success d-flex align-items-center justify-content-between p-3 rounded-4 mb-3 border-0 shadow-sm" style="background: rgba(16, 185, 129, 0.12); color: #065F46;">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-patch-check-fill fs-3 text-success"></i>
                    <div>
                        <strong class="d-block" style="font-size: 0.88rem;">Pencairan Janjian Dana Besar ({{ $prefilledRequest->request_code }})</strong>
                        <small class="text-secondary" style="font-size: 0.76rem;">
                            Nominal Disetujui: <strong class="text-success">Rp {{ number_format($prefilledRequest->amount, 0, ',', '.') }}</strong>
                            @if($prefilledRequest->purpose)
                                · {{ $prefilledRequest->purpose }}
                            @endif
                        </small>
                    </div>
                </div>
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size: 0.72rem;">Disetujui Penuh</span>
            </div>
            @endif

            <!-- Dynamic Scanned / Fetched Student Preview Card -->
            <div id="studentPreviewCard" class="saver-preview-card d-none">
                <div class="avatar-box" id="previewAvatar">A</div>
                <div class="cust-info">
                    <strong id="previewName">Nama Siswa</strong>
                    <span id="previewMeta">NIS: - · Kelas: -</span>
                </div>
                <div class="cust-balance">
                    <strong id="previewBalance">Rp 0</strong>
                    <span id="previewBalanceLabel">Saldo tersedia</span>
                </div>
            </div>

            <form id="withdrawalForm" method="POST" action="{{ route('teller.penarikan.store') }}">
                @csrf

                <!-- Hidden Input ID Janjian Penarikan jika dicairkan via Janjian -->
                <input type="hidden" name="withdrawal_request_id" id="withdrawal_request_id" value="{{ old('withdrawal_request_id', $prefilledRequest->id ?? request('request_id')) }}">

                <!-- 1. Input Nomor Rekening dengan Tombol Scan QR -->
                <div class="field-group">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="field-label mb-0" for="account_number">Nomor Rekening Nasabah</label>
                        <button type="button" class="btn-scan-action-amber" id="btnOpenScanner" title="Buka Kamera Scan QR">
                            <i class="bi bi-qr-code-scan"></i> Scan QR Siswa
                        </button>
                    </div>

                    <div class="field-wrap position-relative">
                        <i class="bi bi-credit-card-2-front-fill" id="accountIcon"></i>
                        <input
                            type="text"
                            id="account_number"
                            name="account_number"
                            class="field-input @error('account_number') is-invalid @enderror"
                            placeholder="Ketik Rekening / NIS atau Scan QR"
                            value="{{ old('account_number', request('account_number')) }}"
                            required
                            autocomplete="off"
                        >
                        <!-- Badge Terkunci Saat Berhasil Scan -->
                        <span id="scanLockBadge" class="badge bg-warning text-dark position-absolute end-0 me-3 d-none align-items-center gap-1 shadow-sm" style="border-radius: 8px; font-size: 0.72rem; padding: 5px 9px;">
                            <i class="bi bi-lock-fill"></i> Terkunci (QR)
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <p class="field-helper mb-0" id="accountHelperText">Masukkan nomor rekening nasabah yang akan melakukan penarikan.</p>
                        <!-- Tombol Reset / Batal -->
                        <button type="button" class="btn btn-sm text-danger p-0 d-none text-decoration-none fw-semibold" id="btnResetAccount" style="font-size: 0.78rem;">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Batal / Ganti Rekening
                        </button>
                    </div>

                    @error('account_number')
                        <div style="color: #dc2626; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                </div>

                <!-- 2. Auto-Populated Input: Nama Lengkap Siswa -->
                <div class="field-group" id="groupStudentName" style="display: none;">
                    <label class="field-label" for="student_name_display">Nama Lengkap Siswa</label>
                    <div class="field-wrap">
                        <i class="bi bi-person-badge-fill"></i>
                        <input
                            type="text"
                            id="student_name_display"
                            class="field-input"
                            readonly
                            placeholder="Nama siswa otomatis terisi"
                        >
                    </div>
                </div>

                <!-- 3. Auto-Populated Input: Saldo Saat Ini -->
                <div class="field-group" id="groupCurrentBalance" style="display: none;">
                    <label class="field-label" for="current_balance_display">Saldo Rekening Tersedia</label>
                    <div class="field-wrap">
                        <span class="field-prefix">Rp</span>
                        <input
                            type="text"
                            id="current_balance_display"
                            class="field-input with-prefix fw-bold text-success"
                            readonly
                            placeholder="0"
                        >
                    </div>
                </div>

                <!-- 4. Nominal Penarikan -->
                <div class="field-group">
                    <label class="field-label" for="amount">Nominal Penarikan</label>
                    <div class="field-wrap">
                        <span class="field-prefix">Rp</span>
                        <input
                            type="number"
                            id="amount"
                            name="amount"
                            min="1000"
                            max="{{ (int) ($uangFisikDiLaci ?? 0) }}"
                            step="500"
                            class="field-input with-prefix @error('amount') is-invalid @enderror"
                            placeholder="0"
                            value="{{ old('amount', (isset($prefilledRequest) && $prefilledRequest) ? (int)$prefilledRequest->amount : request('amount')) }}"
                            required
                        >
                    </div>
                    @error('amount')
                        <div style="color: #dc2626; font-size: 0.75rem; margin-top: 4px;">{{ $message }}</div>
                    @enderror
                    <p class="field-helper" id="amountHelperText">
                        <i class="bi bi-info-circle me-1"></i> Batas harian reguler: <strong>Rp 1.000.000/hari</strong>. Saldo wajib mengendap di rekening minimal <strong>Rp 10.000</strong>. Penarikan &gt; Rp 1.000.000 wajib memiliki Janjian H-1. Maksimal uang fisik laci: <strong>Rp {{ number_format($uangFisikDiLaci ?? 0, 0, ',', '.') }}</strong>.
                    </p>
                </div>

                <!-- Hidden PIN Field in Form, populated from PIN modal -->
                <input type="hidden" name="security_pin" id="form_security_pin">

                <button type="button" class="btn-submit-amber mb-2 mt-2" id="btnOpenPinModal">
                    <i class="bi bi-shield-lock-fill me-1"></i> Verifikasi PIN Nasabah & Lanjutkan
                </button>

                <div class="text-center mt-3">
                    <button type="button" class="btn btn-sm btn-link text-decoration-none text-muted" data-bs-toggle="modal" data-bs-target="#modalLupaPin">
                        <i class="bi bi-question-circle"></i> Nasabah Lupa PIN? Reset di sini
                    </button>
                </div>
            </form>

        </div>
    </div>

    <!-- ===== Modal QR Code Scanner ===== -->
    <div class="modal fade" id="qrScannerModal" tabindex="-1" aria-labelledby="qrScannerModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 26px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.25); overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-4 px-4 align-items-center">
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="qrScannerModalLabel">
                            <i class="bi bi-qr-code-scan me-2" style="color: #B45309;"></i>Scan QR Kartu Siswa
                        </h5>
                        <p class="text-muted small mb-0">Arahkan kamera ke QR Code pada kartu fisik atau aplikasi nasabah</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="btnCloseScannerTop"></button>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="scanner-viewport-box">
                        <div id="qr-scanner-viewport"></div>
                        <div class="scanner-laser-line" id="scannerLaser"></div>
                        <div id="scannerLoading" class="text-white text-center py-5 d-none">
                            <div class="spinner-border mb-2" role="status" style="color: #F59E0B;"></div>
                            <p class="small mb-0 text-white-50">Menginisialisasi modul kamera...</p>
                        </div>
                    </div>

                    <div id="scannerAlert" class="alert alert-info py-2 px-3 mt-3 small mb-0 d-flex align-items-center gap-2" style="border-radius: 12px; font-size: 0.78rem;">
                        <i class="bi bi-camera-video-fill text-warning"></i>
                        <span id="scannerStatusText">Kamera siap. Posisikan QR Code di dalam area kotak scanner.</span>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-1">
                    <button type="button" class="btn btn-light rounded-pill px-4 flex-fill" data-bs-dismiss="modal" id="btnCloseScanner">
                        Tutup Kamera
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Modal PIN Verification Prompt ===== -->
    <div class="modal fade" id="modalPinPrompt" tabindex="-1" aria-labelledby="pinModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 26px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.22); overflow: hidden;">
                <div class="modal-header border-0 pb-0 text-center flex-column pt-4">
                    <div style="width: 60px; height: 60px; border-radius: 20px; background: rgba(245, 158, 11, 0.12); color: #B45309; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 12px;">
                        <i class="bi bi-shield-lock"></i>
                    </div>
                    <h5 class="modal-title fw-bold text-dark" id="pinModalLabel">Otorisasi PIN Nasabah</h5>
                    <p class="text-muted small px-3 mb-0">Silakan arahkan nasabah / siswa untuk memasukkan 6 digit PIN rahasia.</p>
                </div>

                <div class="modal-body px-4 py-3">
                    <div class="p-3 mb-3 rounded-4" style="background: #F8FAFC; border: 1px dashed #CBD5E1;">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">No. Rekening:</span>
                            <span class="fw-bold text-dark" id="previewRek">-</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Nama Nasabah:</span>
                            <span class="fw-bold text-dark" id="previewPinCustName">-</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Total Penarikan:</span>
                            <span class="fw-bold text-danger fs-5" id="previewNominal">Rp 0</span>
                        </div>
                    </div>

                    <div class="mb-3 text-center">
                        <label class="form-label fw-semibold text-dark small mb-2">MASUKKAN 6-DIGIT PIN</label>
                        <div class="position-relative d-inline-block w-100">
                            <input
                                type="password"
                                id="modal_pin_input"
                                class="form-control text-center fw-bold text-dark"
                                style="font-size: 1.8rem; letter-spacing: 12px; border-radius: 16px; border: 2px solid #E2E8F0; padding: 12px;"
                                maxlength="6"
                                pattern="[0-9]{6}"
                                inputmode="numeric"
                                placeholder="••••••"
                                autocomplete="off"
                                autofocus
                            >
                        </div>
                        <small class="text-muted d-block mt-2"><i class="bi bi-lock-fill"></i> PIN nasabah diamankan dengan enkripsi hash.</small>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0 gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 flex-fill" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-warning rounded-pill px-4 flex-fill fw-bold text-white" id="btnSubmitWithdrawal" style="background: linear-gradient(135deg, #B45309 0%, #D97706 100%);">
                        <i class="bi bi-check2-circle me-1"></i> Otorisasi & Tarik
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== Modal Lupa PIN Teller ===== -->
    <div class="modal fade" id="modalLupaPin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 20px; border: none; box-shadow: 0 20px 40px rgba(0,0,0,0.15); overflow: hidden;">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-key-fill text-warning me-2"></i>Reset PIN Nasabah (Lupa PIN)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('teller.savers.reset-pin') }}" method="POST">
                    @csrf
                    <div class="modal-body py-3">
                        <p class="small text-muted mb-3">
                            Petugas Teller berwenang mereset PIN nasabah yang lupa menjadi default <code>123456</code> setelah mencocokkan identitas siswa.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nomor Rekening Nasabah</label>
                            <input type="text" name="account_number" class="form-control" placeholder="Contoh: 20260001" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-warning rounded-pill px-4 text-white fw-bold">Reset PIN ke 123456</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== Modal Struk Penarikan (Receipt) ===== -->
    @if(session('receipt'))
    <div class="modal fade show" id="modalReceipt" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 60px rgba(0,0,0,0.25);">
                <div class="modal-body p-4 text-center" id="printableReceipt">
                    <div class="mb-2"><i class="bi bi-bank2 fs-2 text-primary"></i></div>
                    <h6 class="fw-bold mb-0 text-dark">BANK MINI SEKOLAH</h6>
                    <small class="text-muted d-block mb-3">STRUK TRANSAKSI RESMI</small>
                    <hr style="border-top: 1px dashed #94A3B8;">

                    <div class="text-start small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">No. Transaksi:</span>
                            <span class="fw-semibold">#TRX-{{ session('receipt')['id'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Waktu:</span>
                            <span>{{ session('receipt')['date'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Teller:</span>
                            <span>{{ session('receipt')['teller_name'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">No. Rekening:</span>
                            <span class="fw-bold">{{ session('receipt')['account_number'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">Nama Nasabah:</span>
                            <span class="fw-bold">{{ session('receipt')['student_name'] }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Jenis Transaksi:</span>
                            <span class="fw-semibold text-danger">{{ session('receipt')['type'] }}</span>
                        </div>

                        <div class="p-2 rounded-3 bg-light text-center mb-2">
                            <small class="text-muted d-block">Jumlah Penarikan:</small>
                            <span class="fs-5 fw-bold text-danger">Rp {{ number_format(session('receipt')['amount'], 0, ',', '.') }}</span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Sisa Saldo:</span>
                            <span class="fw-bold text-success">Rp {{ number_format(session('receipt')['current_balance'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <hr style="border-top: 1px dashed #94A3B8;">
                    <small class="text-muted d-block text-center mb-3">Simpan struk ini sebagai bukti transaksi penarikan yang sah.</small>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary rounded-pill flex-fill" onclick="document.getElementById('modalReceipt').style.display='none'">Tutup</button>
                        <button type="button" class="btn btn-primary rounded-pill flex-fill" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i> Cetak Struk
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
                            <div class="d-flex justify-content-between align-items-center small">
                                <span class="text-muted">Sisa Uang Fisik di Laci Saat Ini:</span>
                                <span class="fw-bold text-success fs-6">Rp {{ number_format($uangFisikDiLaci ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">Nominal Tambahan Uang Kas (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-dark">Rp</span>
                                <input type="text" name="amount" class="form-control rupiah-input fw-bold fs-5 text-success" placeholder="Contoh: 500.000" required>
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

@endsection

@push('scripts')
<!-- Library HTML5-QRCode Scanner -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // DOM Elements - Scanner
    const btnOpenScanner       = document.getElementById('btnOpenScanner');
    const qrModalEl            = document.getElementById('qrScannerModal');
    const qrModal              = new bootstrap.Modal(qrModalEl);
    const scannerLaser         = document.getElementById('scannerLaser');
    const scannerAlert         = document.getElementById('scannerAlert');
    const scannerStatusText    = document.getElementById('scannerStatusText');
    const scannerLoading       = document.getElementById('scannerLoading');

    // DOM Elements - Form & Preview
    const accInput             = document.getElementById('account_number');
    const amountInput          = document.getElementById('amount');
    const scanLockBadge        = document.getElementById('scanLockBadge');
    const btnResetAccount      = document.getElementById('btnResetAccount');
    const studentPreviewCard   = document.getElementById('studentPreviewCard');
    const previewAvatar        = document.getElementById('previewAvatar');
    const previewName          = document.getElementById('previewName');
    const previewMeta          = document.getElementById('previewMeta');
    const previewBalance       = document.getElementById('previewBalance');
    const groupStudentName     = document.getElementById('groupStudentName');
    const studentNameDisplay   = document.getElementById('student_name_display');
    const groupCurrentBalance  = document.getElementById('groupCurrentBalance');
    const currentBalanceDisplay= document.getElementById('current_balance_display');

    // DOM Elements - PIN Verification
    const btnOpenPinModal      = document.getElementById('btnOpenPinModal');
    const modalPinPrompt       = new bootstrap.Modal(document.getElementById('modalPinPrompt'));
    const modalPinInput        = document.getElementById('modal_pin_input');
    const formPinInput         = document.getElementById('form_security_pin');
    const btnSubmitWithdrawal  = document.getElementById('btnSubmitWithdrawal');
    const withdrawalForm       = document.getElementById('withdrawalForm');
    const previewRek           = document.getElementById('previewRek');
    const previewPinCustName   = document.getElementById('previewPinCustName');
    const previewNominal       = document.getElementById('previewNominal');

    let html5QrCode = null;
    let isScanning = false;

    // Gentle POS scanner beep via Web Audio API
    function playBeep() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.type = 'sine';
            osc.frequency.setValueAtTime(880, ctx.currentTime);
            gain.gain.setValueAtTime(0.12, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.15);
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.start();
            osc.stop(ctx.currentTime + 0.15);
        } catch (e) {
            // Audio context unsupported / muted
        }
    }

    // Parse Scanned Data (handles JSON or plain text)
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

    // Backend AJAX Retrieval & Form State Lock
    async function fetchSaverData(identifier, isFromScanner = false) {
        if (!identifier) return;

        try {
            const helper = document.getElementById('accountHelperText');
            helper.innerHTML = '<span class="text-warning"><span class="spinner-border spinner-border-sm me-1"></span> Mengambil data nasabah...</span>';

            const reqIdParam = document.getElementById('withdrawal_request_id')?.value || urlParams.get('request_id') || '';
            const url = `{{ route('teller.search-saver') }}?q=${encodeURIComponent(identifier)}` + (reqIdParam ? `&request_id=${encodeURIComponent(reqIdParam)}` : '');
            const response = await fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (response.ok && result.success && result.data) {
                const saver = result.data;

                // 1. Auto-Fill Nilai Form
                accInput.value = saver.account_number || saver.student_id;
                studentNameDisplay.value = saver.student_name || saver.name;
                currentBalanceDisplay.value = Number(saver.saldo ?? saver.balance ?? 0).toLocaleString('id-ID');

                // 2. Tampilkan Card Preview Nasabah
                previewAvatar.textContent = (saver.student_name || 'S').charAt(0).toUpperCase();
                previewName.textContent = saver.student_name || saver.name;
                previewMeta.textContent = `NIS: ${saver.student_id || saver.nis || '-'} · ${saver.grade_class || '-'}`;
                previewBalance.textContent = saver.formatted_saldo || ('Rp ' + Number(saver.saldo || 0).toLocaleString('id-ID'));

                studentPreviewCard.classList.remove('d-none');
                groupStudentName.style.display = 'block';
                groupCurrentBalance.style.display = 'block';

                // 3. Kunci Input Field (Readonly) jika dari scanner
                if (isFromScanner) {
                    accInput.setAttribute('readonly', 'readonly');
                    scanLockBadge.classList.remove('d-none');
                    scanLockBadge.classList.add('d-inline-flex');
                    helper.innerHTML = '<span class="text-success"><i class="bi bi-shield-check"></i> Identitas nasabah diverifikasi & dikunci dari QR Code.</span>';
                } else {
                    helper.innerHTML = '<span class="text-success"><i class="bi bi-check-circle"></i> Data nasabah berhasil dimuat.</span>';
                }

                // Cek status blokir
                if (saver.is_blocked) {
                    helper.innerHTML = '<div class="alert alert-danger py-2 px-3 mt-2 mb-0 small"><i class="bi bi-lock-fill me-1"></i> <strong>REKENING DIBLOKIR:</strong> Transaksi penarikan dinonaktifkan oleh Administrator.</div>';
                    btnOpenPinModal.setAttribute('disabled', 'disabled');
                } else {
                    btnOpenPinModal.removeAttribute('disabled');
                    
                    // Tampilkan info kuota harian & sisa saldo mengendap
                    let infoHtml = `<div class="mt-2 text-muted" style="font-size:0.75rem;">
                        <span>Maksimal bisa ditarik (Saldo - Rp 10.000): <strong class="text-success">${saver.formatted_max_withdraw}</strong></span> · 
                        <span>Sisa kuota harian reguler: <strong class="text-primary">Rp ${Number(saver.remaining_daily_quota || 0).toLocaleString('id-ID')}</strong></span>
                    </div>`;

                    if (saver.approved_request) {
                        const ar = saver.approved_request;
                        const reqIdField = document.getElementById('withdrawal_request_id');
                        if (reqIdField && !reqIdField.value) {
                            reqIdField.value = ar.id;
                        }
                        infoHtml += `<div class="mt-2 p-2 rounded-3 bg-warning-subtle border border-warning small text-dark d-flex align-items-center justify-content-between">
                            <div><i class="bi bi-calendar-check-fill text-warning me-1"></i> <strong>Janjian Dana Besar (${ar.request_code}):</strong> Rp ${Number(ar.amount).toLocaleString('id-ID')}</div>
                            <button type="button" class="btn btn-sm btn-dark py-0 px-2 fw-semibold" style="font-size:0.74rem;" onclick="document.getElementById('amount').value = ${ar.amount}; if(document.getElementById('withdrawal_request_id')) document.getElementById('withdrawal_request_id').value = ${ar.id};">Gunakan Nominal Ini</button>
                        </div>`;

                        // Otomatis terapkan nominal pengajuan jika amountInput masih kosong atau 0
                        if (!amountInput.value || amountInput.value === '0') {
                            amountInput.value = ar.amount;
                        }
                    }
                    helper.innerHTML += infoHtml;
                }

                // 4. Munculkan Tombol Reset / Batal
                btnResetAccount.classList.remove('d-none');

                // 5. Pastikan parameter URL amount dipertahankan jika ada
                const paramAmount = urlParams.get('amount');
                if (paramAmount) {
                    amountInput.value = paramAmount;
                }

                // 6. Otomatis Fokus ke Field Nominal Penarikan
                setTimeout(() => {
                    amountInput.focus();
                    amountInput.select();
                }, 150);

            } else {
                throw new Error(result.message || 'Data nasabah tidak ditemukan di sistem.');
            }

        } catch (err) {
            alert(err.message || 'Gagal mencari nasabah. Periksa koneksi atau nomor rekening.');
            document.getElementById('accountHelperText').textContent = 'Masukkan nomor rekening nasabah yang akan melakukan penarikan.';
            if (isFromScanner) {
                unlockForm();
            }
        }
    }

    // Stop Scanner Safely
    function stopScanner() {
        if (html5QrCode && isScanning) {
            html5QrCode.stop().then(() => {
                html5QrCode.clear();
                isScanning = false;
                scannerLaser.style.display = 'none';
            }).catch(err => console.error("Error stopping scanner:", err));
        }
    }

    // Reset and Unlock Form Mechanism
    function unlockForm() {
        accInput.value = '';
        accInput.removeAttribute('readonly');
        scanLockBadge.classList.add('d-none');
        scanLockBadge.classList.remove('d-inline-flex');
        
        studentPreviewCard.classList.add('d-none');
        groupStudentName.style.display = 'none';
        groupCurrentBalance.style.display = 'none';
        btnResetAccount.classList.add('d-none');

        studentNameDisplay.value = '';
        currentBalanceDisplay.value = '';
        amountInput.value = '';
        if (document.getElementById('withdrawal_request_id')) {
            document.getElementById('withdrawal_request_id').value = '';
        }
        const bannerReq = document.getElementById('bannerJanjianPenarikan');
        if (bannerReq) bannerReq.style.display = 'none';

        document.getElementById('accountHelperText').textContent = 'Masukkan nomor rekening nasabah yang akan melakukan penarikan.';
        accInput.focus();
    }

    btnResetAccount.addEventListener('click', function () {
        if (confirm('Batal memilih rekening ini dan reset form?')) {
            unlockForm();
        }
    });

    // Modal Events: Start and Stop Camera
    btnOpenScanner.addEventListener('click', function () {
        qrModal.show();
    });

    qrModalEl.addEventListener('shown.bs.modal', function () {
        scannerLaser.style.display = 'block';
        scannerLoading.classList.remove('d-none');
        scannerStatusText.textContent = 'Menghubungkan kamera perangkat...';

        html5QrCode = new Html5Qrcode("qr-scanner-viewport");
        const config = { fps: 15, qrbox: { width: 250, height: 250 }, aspectRatio: 1.0 };

        html5QrCode.start(
            { facingMode: "environment" },
            config,
            (decodedText) => {
                playBeep();
                const identifier = parseIdentifier(decodedText);
                stopScanner();
                qrModal.hide();
                fetchSaverData(identifier, true);
            },
            (errorMessage) => {
                // frame read loop, ignore
            }
        ).then(() => {
            isScanning = true;
            scannerLoading.classList.add('d-none');
            scannerStatusText.textContent = 'Kamera aktif. Posisikan QR Code di dalam kotak scanner.';
        }).catch((err) => {
            scannerLoading.classList.add('d-none');
            scannerLaser.style.display = 'none';
            scannerAlert.className = 'alert alert-danger py-2 px-3 mt-3 small mb-0 d-flex align-items-center gap-2';
            scannerStatusText.textContent = 'Gagal mengakses kamera: ' + (err.name || err.message || err);
        });
    });

    qrModalEl.addEventListener('hidden.bs.modal', function () {
        stopScanner();
    });

    // Fallback: Jika Teller mengetik manual dan menekan Enter atau Tab
    accInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            if (this.value.trim() && !this.hasAttribute('readonly')) {
                fetchSaverData(this.value.trim(), false);
            }
        }
    });

    accInput.addEventListener('blur', function () {
        if (this.value.trim() && !this.hasAttribute('readonly') && studentPreviewCard.classList.contains('d-none')) {
            fetchSaverData(this.value.trim(), false);
        }
    });

    // PIN Verification Modal Handlers
    btnOpenPinModal.addEventListener('click', function () {
        if (!accInput.value.trim()) {
            alert('Silakan scan QR Code atau isi Nomor Rekening nasabah terlebih dahulu.');
            accInput.focus();
            return;
        }
        if (!amountInput.value || parseInt(amountInput.value) < 1000) {
            alert('Silakan isi nominal penarikan minimal Rp 1.000.');
            amountInput.focus();
            return;
        }
        const maxDrawerCash = {{ (float) ($uangFisikDiLaci ?? 0) }};
        if (maxDrawerCash > 0 && parseInt(amountInput.value) > maxDrawerCash) {
            alert('Transaksi Ditolak: Nominal penarikan (Rp ' + parseInt(amountInput.value).toLocaleString('id-ID') + ') melebihi uang fisik di laci kas saat ini (Maksimal: Rp ' + maxDrawerCash.toLocaleString('id-ID') + '). Silakan lakukan Tambah Kas Laci jika perlu.');
            amountInput.focus();
            return;
        }

        previewRek.textContent = accInput.value;
        previewPinCustName.textContent = studentNameDisplay.value || (previewName.textContent !== 'Nama Siswa' ? previewName.textContent : '-');
        previewNominal.textContent = 'Rp ' + parseInt(amountInput.value).toLocaleString('id-ID');
        modalPinInput.value = '';

        modalPinPrompt.show();
        setTimeout(() => modalPinInput.focus(), 500);
    });

    btnSubmitWithdrawal.addEventListener('click', function () {
        if (modalPinInput.value.length !== 6) {
            alert('PIN keamanan nasabah harus terdiri dari 6 digit angka.');
            modalPinInput.focus();
            return;
        }

        formPinInput.value = modalPinInput.value;
        withdrawalForm.submit();
    });

    modalPinInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            btnSubmitWithdrawal.click();
        }
    });

    // Auto-search jika ada parameter di URL (misal dari dashboard atau Janjian Penarikan)
    const urlParams = new URLSearchParams(window.location.search);
    const initialAcc = urlParams.get('account_number') || accInput.value.trim();
    const initialAmount = urlParams.get('amount');
    const initialReqId = urlParams.get('request_id');

    if (initialReqId && document.getElementById('withdrawal_request_id')) {
        document.getElementById('withdrawal_request_id').value = initialReqId;
    }
    if (initialAmount) {
        amountInput.value = initialAmount;
    }

    if (initialAcc) {
        fetchSaverData(initialAcc, true);
    }
});
</script>
@endpush