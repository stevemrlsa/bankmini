<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Profil &amp; Keamanan Akun — Bank Mini SMKN 1 Bantul</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-smkn1bantul.png') }}">

    <!-- PWA & Mobile Web App Meta Tags -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#16233F">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-navy: #16233F;
            --primary-navy-dark: #0A1426;
            --primary-navy-light: #24355A;
            --accent-cyan: #0EA5E9;
            --accent-gold: #F59E0B;
            --bg-page: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-soft: #E2E8F0;
            --radius-xl: 24px;
            --radius-lg: 18px;
            --radius-md: 14px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            overflow-x: hidden;
            width: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
            padding-bottom: 30px;
        }

        /* ===== Top Navigation Bar ===== */
        .portal-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-soft);
            position: sticky;
            top: 0;
            z-index: 1020;
            padding: 0 env(safe-area-inset-right) 0 env(safe-area-inset-left);
        }

        .header-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .brand-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: var(--text-dark);
            min-width: 0;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            flex-shrink: 0;
        }

        .brand-text strong {
            display: block;
            font-size: 0.98rem;
            font-weight: 700;
            color: var(--primary-navy);
            line-height: 1.2;
            white-space: nowrap;
        }

        .brand-text span {
            font-size: 0.72rem;
            color: var(--text-muted);
            white-space: nowrap;
            display: block;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }

        .user-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #EFF6FF;
            border: 1px solid #BFDBFE;
            padding: 5px 14px 5px 6px;
            border-radius: 50px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #1E3A8A;
            text-decoration: none;
            transition: all 0.15s ease;
        }

        .user-avatar-circle {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-navy) 0%, #1E3A8A 100%);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.76rem;
            font-weight: 700;
        }

        .btn-header-action {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 1px solid var(--border-soft);
            background: #FFFFFF;
            color: var(--text-dark);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.95rem;
            text-decoration: none;
            transition: all 0.18s ease;
            cursor: pointer;
            flex-shrink: 0;
        }

        .btn-header-action:hover {
            background: #F8FAFC;
            border-color: var(--primary-navy);
            color: var(--primary-navy);
            transform: translateY(-1px);
        }

        .btn-header-action.text-danger:hover {
            background: #FEE2E2;
            border-color: #EF4444;
            color: #DC2626 !important;
        }

        /* ===== Main Container ===== */
        .portal-main {
            max-width: 1000px;
            margin: 0 auto;
            padding: 24px 18px;
            width: 100%;
            flex-grow: 1;
        }

        /* Profile Banner */
        .profile-banner {
            background: linear-gradient(135deg, #0A1426 0%, #16233F 55%, #1E3A8A 100%);
            border-radius: var(--radius-xl);
            padding: 26px 28px;
            color: #FFFFFF;
            margin-bottom: 24px;
            box-shadow: 0 10px 25px rgba(22, 35, 63, 0.14);
            position: relative;
            overflow: hidden;
        }

        .profile-banner::after {
            content: '';
            position: absolute;
            right: -30px;
            top: -30px;
            width: 160px;
            height: 160px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.22) 0%, rgba(255, 255, 255, 0) 70%);
            pointer-events: none;
        }

        .profile-avatar-big {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            background: #FFFFFF;
            color: var(--primary-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: 800;
            border: 3px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 4px 14px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }

        .nav-pills-custom .nav-link {
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.84rem;
            padding: 9px 20px;
            color: #64748B;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .nav-pills-custom .nav-link:hover {
            color: var(--primary-navy);
            border-color: #CBD5E1;
        }

        .nav-pills-custom .nav-link.active {
            background: var(--primary-navy);
            color: #FFFFFF;
            border-color: var(--primary-navy);
            box-shadow: 0 4px 12px rgba(22, 35, 63, 0.2);
        }

        .card-custom {
            background: #FFFFFF;
            border-radius: 20px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            overflow: hidden;
        }

        .card-custom-header {
            padding: 18px 24px;
            border-bottom: 1px solid #F1F5F9;
            background: #FFFFFF;
        }

        .card-custom-body {
            padding: 24px;
        }

        .info-field-box {
            background-color: #F8FAFC;
            border: 1px solid var(--border-soft);
            border-radius: 14px;
            padding: 12px 16px;
            height: 100%;
        }

        .info-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #94A3B8;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
            display: block;
        }

        .info-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-dark);
            word-break: break-all;
        }

        .btn-submit-action {
            background: var(--primary-navy);
            color: #FFFFFF;
            border: none;
            border-radius: 50px;
            padding: 10px 24px;
            font-size: 0.88rem;
            font-weight: 600;
            transition: all 0.18s ease;
            box-shadow: 0 4px 14px rgba(22, 35, 63, 0.2);
        }

        .btn-submit-action:hover {
            background: #0A1426;
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        @media (max-width: 768px) {
            .portal-main {
                padding: 16px 12px;
            }

            .profile-banner {
                padding: 20px 18px;
                border-radius: 18px;
            }

            .profile-avatar-big {
                width: 58px;
                height: 58px;
                font-size: 1.6rem;
            }

            .card-custom-body {
                padding: 18px 16px;
            }
        }
    </style>
</head>
<body>

    <!-- ===== TOP HEADER ===== -->
    <header class="portal-header">
        <div class="header-container">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('saver.dashboard') }}" class="btn-header-action" title="Kembali ke Dashboard Tabungan">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <a href="{{ route('saver.dashboard') }}" class="brand-wrap">
                    <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul" class="brand-logo">
                    <div class="brand-text">
                        <strong>Bank Mini</strong>
                        <span>SMKN 1 Bantul — SimPel</span>
                    </div>
                </a>
            </div>

            <!-- Bagian Kanan Atas: HANYA 2 Elemen (Nama Nasabah & Tombol Logout) -->
            <div class="header-actions">
                <a href="{{ route('saver.profile') }}" class="user-pill" title="Profil Nasabah">
                    <div class="user-avatar-circle">
                        {{ strtoupper(substr($saver->student_name, 0, 1)) }}
                    </div>
                    <span>{{ explode(' ', $saver->student_name)[0] }}</span>
                </a>

                <form action="{{ route('saver.logout') }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin keluar dari akun?');">
                    @csrf
                    <button type="submit" class="btn-header-action text-danger" title="Keluar Akun">
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- ===== MAIN CONTENT ===== -->
    <main class="portal-main">

        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" style="border-radius: 14px;" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-3" style="border-radius: 14px;" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('dev_otp_notice'))
            <div class="alert alert-warning border-0 shadow-sm mb-3" style="border-radius: 14px; background-color: #FEF3C7; color: #92400E;" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-info-circle-fill fs-5 me-2 flex-shrink-0 mt-1 text-warning"></i>
                    <div>
                        <strong>Mode Pengujian / Dev:</strong><br>
                        {{ session('dev_otp_notice') }}
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-3" style="border-radius: 14px;" role="alert">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-octagon-fill fs-5 me-2 flex-shrink-0 mt-1"></i>
                    <div>
                        <strong>Perhatian:</strong>
                        <ul class="mb-0 ps-3 mt-1 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Profile Header Banner -->
        <div class="profile-banner">
            <div class="d-flex align-items-center gap-3 gap-md-4 flex-wrap">
                <div class="profile-avatar-big">
                    {{ strtoupper(substr($saver->student_name, 0, 1)) }}
                </div>
                <div class="flex-grow-1 min-w-0">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h4 class="fw-bold mb-0 text-white text-truncate">{{ $saver->student_name }}</h4>
                        <span class="badge bg-light text-dark px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.76rem;">
                            <i class="bi bi-mortarboard-fill text-primary me-1"></i>{{ $saver->grade_class ?? 'Siswa' }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.74rem;">
                            <i class="bi bi-check-circle-fill me-1"></i>{{ ucfirst($saver->status ?? 'Aktif') }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-3 text-white-50 small flex-wrap">
                        <span><i class="bi bi-credit-card me-1"></i>Rekening: <strong class="text-white">{{ $saver->account_number }}</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-person-vcard me-1"></i>NIS: <strong class="text-white">{{ $saver->student_id }}</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-wallet2 me-1"></i>Saldo: <strong class="text-white">Rp {{ number_format($saver->saldo, 0, ',', '.') }}</strong></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        @php
            $activeTab = 'info';
            if (session('saver_pin_otp_sent')) {
                $activeTab = 'pin';
            } elseif (session('saver_pw_otp_sent')) {
                $activeTab = 'password';
            } elseif (session('saver_change_email_otp_sent')) {
                $activeTab = 'email';
            }
        @endphp

        <div class="d-flex overflow-x-auto pb-2 mb-3 gap-2">
            <ul class="nav nav-pills-custom flex-nowrap" id="saverProfileTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'info' ? 'active' : '' }}" id="tab-info-btn" data-bs-toggle="pill" data-bs-target="#pane-info" type="button" role="tab">
                        <i class="bi bi-person-lines-fill me-1"></i> Data Identitas
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'pin' ? 'active' : '' }}" id="tab-pin-btn" data-bs-toggle="pill" data-bs-target="#pane-pin" type="button" role="tab">
                        <i class="bi bi-shield-lock-fill me-1"></i> Ganti PIN (OTP)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'password' ? 'active' : '' }}" id="tab-password-btn" data-bs-toggle="pill" data-bs-target="#pane-password" type="button" role="tab">
                        <i class="bi bi-key-fill me-1"></i> Ganti Password (OTP)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'email' ? 'active' : '' }}" id="tab-email-btn" data-bs-toggle="pill" data-bs-target="#pane-email" type="button" role="tab">
                        <i class="bi bi-envelope-at-fill me-1"></i> Ganti Email (OTP)
                    </button>
                </li>
            </ul>
        </div>

        <!-- Tab Panes Content -->
        <div class="tab-content" id="saverProfileTabContent">

            <!-- TAB 1: DATA IDENTITAS LENGKAP -->
            <div class="tab-pane fade {{ $activeTab === 'info' ? 'show active' : '' }}" id="pane-info" role="tabpanel">
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-person-badge text-primary"></i> Data Identitas Akun Nasabah
                        </h6>
                        <small class="text-muted">Informasi resmi identitas rekening tabungan SimPel SMKN 1 Bantul Anda</small>
                    </div>
                    <div class="card-custom-body">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Nama Lengkap Siswa</span>
                                    <div class="info-value">{{ $saver->student_name }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Username Login</span>
                                    <div class="info-value">{{ $saver->username ?: ('siswa_' . $saver->student_id) }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Nomor Rekening SimPel</span>
                                    <div class="info-value font-monospace">{{ $saver->account_number }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Nomor Induk Siswa (NIS)</span>
                                    <div class="info-value font-monospace">{{ $saver->student_id }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Alamat Email Terdaftar</span>
                                    <div class="info-value">
                                        @if(!empty($saver->email))
                                            <i class="bi bi-envelope-check text-success me-1"></i>{{ $saver->email }}
                                        @else
                                            <span class="text-danger small"><i class="bi bi-exclamation-circle me-1"></i>Belum terdaftar (Silakan gunakan tab Ganti Email)</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Kelas / Program Keahlian</span>
                                    <div class="info-value">{{ $saver->grade_class ?? 'Siswa' }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Saldo Tabungan Saat Ini</span>
                                    <div class="info-value text-success fw-bold">Rp {{ number_format($saver->saldo, 0, ',', '.') }}</div>
                                </div>
                            </div>

                            <div class="col-sm-6">
                                <div class="info-field-box">
                                    <span class="info-label">Status Keanggotaan</span>
                                    <div class="info-value">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">
                                            <i class="bi bi-check-circle-fill me-1"></i>{{ ucfirst($saver->status ?? 'Aktif') }} (Terdaftar)
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 2: GANTI PIN 6-DIGIT (OTP BATAS 2 MENIT) -->
            <div class="tab-pane fade {{ $activeTab === 'pin' ? 'show active' : '' }}" id="pane-pin" role="tabpanel">
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-shield-lock-fill text-primary"></i> Ganti PIN Keamanan 6-Digit (Transaksi Loket)
                        </h6>
                        <small class="text-muted">PIN digunakan saat melakukan transaksi penarikan saldo di loket Teller Bank Mini</small>
                    </div>
                    <div class="card-custom-body">
                        @if(!session('saver_pin_otp_sent'))
                            {{-- Step 1: Kirim OTP PIN --}}
                            <div class="text-center py-3" style="max-width: 480px; margin: 0 auto;">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 60px; height: 60px; background: #EFF6FF; color: #1E3A8A;">
                                    <i class="bi bi-send-check fs-2"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Kirim Kode OTP Ganti PIN</h6>
                                <p class="text-muted small mb-3">
                                    Sistem akan mengirimkan 6 digit kode OTP verifikasi ke email akun Anda: <br>
                                    <strong class="text-primary">{{ $saver->email ?: 'Email belum diatur' }}</strong>. <br>
                                    Batas waktu aktif kode OTP adalah <strong>1 sampai 2 menit</strong>.
                                </p>

                                @if(empty($saver->email))
                                    <div class="alert alert-warning py-2 small mb-3">
                                        Alamat email Anda belum diatur. Silakan perbarui alamat email terlebih dahulu pada tab <strong>Ganti Email</strong>.
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('saver.profile.pin.otp') }}">
                                        @csrf
                                        <button type="submit" class="btn-submit-action">
                                            <i class="bi bi-envelope-paper me-1"></i> Kirim Kode OTP Sekarang
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @else
                            {{-- Step 2: Input OTP & PIN Baru dengan Countdown Timer 2 Menit --}}
                            <div style="max-width: 460px; margin: 0 auto;">
                                <div class="p-3 mb-4 text-center rounded-3 border" style="background-color: #F8FAFC;">
                                    <small class="text-muted d-block mb-1">Batas waktu aktif kode OTP ganti PIN:</small>
                                    <span id="pinCountdownBadge" class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 font-monospace fs-6 fw-bold">
                                        <i class="bi bi-stopwatch me-1"></i> <span id="pinTimerDisplay">02:00</span>
                                    </span>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Waktu sistem: 1–2 menit. Jika habis, silakan kirim ulang OTP.</small>
                                </div>

                                <form method="POST" action="{{ route('saver.profile.pin.update') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-dark">Masukkan 6-Digit Kode OTP <span class="text-danger">*</span></label>
                                        <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                               class="form-control text-center font-monospace fs-5 fw-bold" placeholder="••••••" required autofocus>
                                        <div class="form-text">Periksa Inbox atau Spam email {{ $saver->email }}.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-dark">PIN 6-Digit Baru <span class="text-danger">*</span></label>
                                        <input type="password" name="pin" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                               class="form-control text-center font-monospace fs-5" placeholder="6 Digit Angka" required>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold small text-dark">Ulangi PIN 6-Digit Baru <span class="text-danger">*</span></label>
                                        <input type="password" name="pin_confirmation" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                               class="form-control text-center font-monospace fs-5" placeholder="Ketik ulang 6 digit angka" required>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <button type="submit" class="btn-submit-action">
                                            <i class="bi bi-check-circle-fill me-1"></i> Simpan PIN Baru
                                        </button>

                                        <form method="POST" action="{{ route('saver.profile.pin.otp') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-decoration-none small text-muted">
                                                <i class="bi bi-arrow-repeat me-1"></i> Kirim Ulang OTP
                                            </button>
                                        </form>
                                    </div>
                                </form>

                                <script>
                                    (function() {
                                        var expiresAt = {{ session('saver_pin_otp_expires_at', time() + 120) }} * 1000;
                                        var timerDisplay = document.getElementById('pinTimerDisplay');
                                        var countdownBadge = document.getElementById('pinCountdownBadge');

                                        function updatePinTimer() {
                                            var now = new Date().getTime();
                                            var distance = expiresAt - now;

                                            if (distance <= 0) {
                                                if (timerDisplay) timerDisplay.innerText = "00:00 (Kedaluwarsa)";
                                                if (countdownBadge) countdownBadge.className = "badge bg-danger text-white px-3 py-2 font-monospace fs-6 fw-bold";
                                                clearInterval(pinInterval);
                                                return;
                                            }

                                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                            var mStr = (minutes < 10 ? "0" : "") + minutes;
                                            var sStr = (seconds < 10 ? "0" : "") + seconds;

                                            if (timerDisplay) timerDisplay.innerText = mStr + ":" + sStr;
                                        }

                                        updatePinTimer();
                                        var pinInterval = setInterval(updatePinTimer, 1000);
                                    })();
                                </script>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 3: GANTI KATA SANDI (OTP BATAS 2 MENIT) -->
            <div class="tab-pane fade {{ $activeTab === 'password' ? 'show active' : '' }}" id="pane-password" role="tabpanel">
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-key-fill text-primary"></i> Ganti Kata Sandi Portal Nasabah
                        </h6>
                        <small class="text-muted">Perbarui kata sandi untuk login mandiri ke portal buku tabungan digital</small>
                    </div>
                    <div class="card-custom-body">
                        @if(!session('saver_pw_otp_sent'))
                            {{-- Step 1: Minta OTP Password --}}
                            <div class="text-center py-3" style="max-width: 480px; margin: 0 auto;">
                                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 60px; height: 60px; background: #EFF6FF; color: #1E3A8A;">
                                    <i class="bi bi-shield-lock fs-2"></i>
                                </div>
                                <h6 class="fw-bold text-dark">Kirim Kode OTP Ganti Kata Sandi</h6>
                                <p class="text-muted small mb-3">
                                    Sistem akan mengirimkan kode OTP keamanan ke alamat email terdaftar: <br>
                                    <strong class="text-primary">{{ $saver->email ?: 'Email belum diatur' }}</strong>. <br>
                                    Batas waktu aktif kode OTP adalah <strong>1 sampai 2 menit</strong>.
                                </p>

                                @if(empty($saver->email))
                                    <div class="alert alert-warning py-2 small mb-3">
                                        Alamat email Anda belum diatur. Silakan atur email terlebih dahulu pada tab <strong>Ganti Email</strong>.
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('saver.profile.password.otp') }}">
                                        @csrf
                                        <button type="submit" class="btn-submit-action">
                                            <i class="bi bi-envelope-paper me-1"></i> Kirim Kode OTP Sekarang
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @else
                            {{-- Step 2: Input OTP & Password Baru dengan Countdown Timer 2 Menit --}}
                            <div style="max-width: 460px; margin: 0 auto;">
                                <div class="p-3 mb-4 text-center rounded-3 border" style="background-color: #F8FAFC;">
                                    <small class="text-muted d-block mb-1">Batas waktu aktif kode OTP ganti kata sandi:</small>
                                    <span id="pwCountdownBadge" class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 font-monospace fs-6 fw-bold">
                                        <i class="bi bi-stopwatch me-1"></i> <span id="pwTimerDisplay">02:00</span>
                                    </span>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Waktu sistem: 1–2 menit. Jika habis, silakan kirim ulang OTP.</small>
                                </div>

                                <form method="POST" action="{{ route('saver.profile.password.update') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-dark">Masukkan 6 Digit Kode OTP <span class="text-danger">*</span></label>
                                        <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                               class="form-control text-center font-monospace fs-5 fw-bold" placeholder="••••••" required autofocus>
                                        <div class="form-text">Periksa Inbox atau Spam email {{ $saver->email }}.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-dark">Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold small text-dark">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ketik ulang kata sandi baru" required>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <button type="submit" class="btn-submit-action">
                                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Kata Sandi Baru
                                        </button>

                                        <form method="POST" action="{{ route('saver.profile.password.otp') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-link text-decoration-none small text-muted">
                                                <i class="bi bi-arrow-repeat me-1"></i> Kirim Ulang OTP
                                            </button>
                                        </form>
                                    </div>
                                </form>

                                <script>
                                    (function() {
                                        var expiresAt = {{ session('saver_pw_otp_expires_at', time() + 120) }} * 1000;
                                        var timerDisplay = document.getElementById('pwTimerDisplay');
                                        var countdownBadge = document.getElementById('pwCountdownBadge');

                                        function updatePwTimer() {
                                            var now = new Date().getTime();
                                            var distance = expiresAt - now;

                                            if (distance <= 0) {
                                                if (timerDisplay) timerDisplay.innerText = "00:00 (Kedaluwarsa)";
                                                if (countdownBadge) countdownBadge.className = "badge bg-danger text-white px-3 py-2 font-monospace fs-6 fw-bold";
                                                clearInterval(pwInterval);
                                                return;
                                            }

                                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                            var mStr = (minutes < 10 ? "0" : "") + minutes;
                                            var sStr = (seconds < 10 ? "0" : "") + seconds;

                                            if (timerDisplay) timerDisplay.innerText = mStr + ":" + sStr;
                                        }

                                        updatePwTimer();
                                        var pwInterval = setInterval(updatePwTimer, 1000);
                                    })();
                                </script>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 4: GANTI EMAIL (VERIFIKASI PASSWORD/PIN + OTP EMAIL BARU BATAS 2 MENIT) -->
            <div class="tab-pane fade {{ $activeTab === 'email' ? 'show active' : '' }}" id="pane-email" role="tabpanel">
                <div class="card-custom">
                    <div class="card-custom-header">
                        <h6 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-envelope-at-fill text-primary"></i> Perbarui Alamat Email Akun
                        </h6>
                        <small class="text-muted">Masukkan email baru dan kata sandi/PIN saat ini untuk verifikasi kepemilikan via kode OTP</small>
                    </div>
                    <div class="card-custom-body">
                        @if(!session('saver_change_email_otp_sent'))
                            {{-- Step 1: Input Email Baru & Password/PIN --}}
                            <div style="max-width: 480px; margin: 0 auto;">
                                <div class="alert alert-info py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                                    <i class="bi bi-info-circle-fill fs-5 me-2 text-primary"></i>
                                    <div>
                                        Email saat ini: <strong>{{ $saver->email ?: 'Belum diatur' }}</strong>.<br>
                                        Masukkan kata sandi atau PIN keamanan saat ini untuk konfirmasi sebelum kode OTP dikirim ke email baru.
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('saver.profile.email.otp') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-dark">Alamat Email Baru <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope-at text-muted"></i></span>
                                            <input type="email" name="new_email" class="form-control" placeholder="contoh: siswa.baru@gmail.com" required value="{{ old('new_email') }}">
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold small text-dark">Kata Sandi atau PIN Akun Saat Ini <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                            <input type="password" name="auth_credential" class="form-control" placeholder="Masukkan password atau PIN akun" required>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn-submit-action">
                                        <i class="bi bi-send-fill me-1"></i> Kirim Kode OTP ke Email Baru
                                    </button>
                                </form>
                            </div>
                        @else
                            {{-- Step 2: Input OTP dari Email Baru dengan Countdown Timer 2 Menit --}}
                            <div style="max-width: 480px; margin: 0 auto;">
                                <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                                    <i class="bi bi-envelope-check-fill fs-5 me-2 text-success"></i>
                                    <div>
                                        Kode OTP verifikasi telah dikirim ke alamat: <br>
                                        <strong>{{ session('saver_change_email_new') }}</strong>
                                    </div>
                                </div>

                                <div class="p-3 mb-4 text-center rounded-3 border" style="background-color: #F8FAFC;">
                                    <small class="text-muted d-block mb-1">Batas waktu aktif kode OTP verifikasi email:</small>
                                    <span id="emailCountdownBadge" class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 font-monospace fs-6 fw-bold">
                                        <i class="bi bi-stopwatch me-1"></i> <span id="emailTimerDisplay">02:00</span>
                                    </span>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Waktu sistem: 1–2 menit. Jika lewat waktu, silakan ulangi permintaan.</small>
                                </div>

                                <form method="POST" action="{{ route('saver.profile.email.update') }}">
                                    @csrf

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold small text-dark">Masukkan 6 Digit Kode OTP dari Email Baru <span class="text-danger">*</span></label>
                                        <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                               class="form-control text-center font-monospace fs-5 fw-bold" placeholder="••••••" required autofocus>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                        <button type="submit" class="btn-submit-action">
                                            <i class="bi bi-check-circle-fill me-1"></i> Verifikasi &amp; Simpan Email Baru
                                        </button>

                                        <a href="{{ route('saver.profile') }}" class="btn btn-link text-decoration-none small text-muted">
                                            <i class="bi bi-x-circle me-1"></i> Batal
                                        </a>
                                    </div>
                                </form>

                                <script>
                                    (function() {
                                        var expiresAt = {{ session('saver_change_email_expires_at', time() + 120) }} * 1000;
                                        var timerDisplay = document.getElementById('emailTimerDisplay');
                                        var countdownBadge = document.getElementById('emailCountdownBadge');

                                        function updateEmailTimer() {
                                            var now = new Date().getTime();
                                            var distance = expiresAt - now;

                                            if (distance <= 0) {
                                                if (timerDisplay) timerDisplay.innerText = "00:00 (Kedaluwarsa)";
                                                if (countdownBadge) countdownBadge.className = "badge bg-danger text-white px-3 py-2 font-monospace fs-6 fw-bold";
                                                clearInterval(emailInterval);
                                                return;
                                            }

                                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                                            var mStr = (minutes < 10 ? "0" : "") + minutes;
                                            var sStr = (seconds < 10 ? "0" : "") + seconds;

                                            if (timerDisplay) timerDisplay.innerText = mStr + ":" + sStr;
                                        }

                                        updateEmailTimer();
                                        var emailInterval = setInterval(updateEmailTimer, 1000);
                                    })();
                                </script>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

