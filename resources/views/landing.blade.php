<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Mini SMKN 1 Bantul — Smart Digital Banking Platform</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-smkn1bantul.png') }}">

    <!-- Bootstrap 5 CSS -->
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
            --primary-navy-dark: #0E1830;
            --primary-navy-light: #24355A;
            --accent-blue: #0284C7;
            --accent-sky: #38BDF8;
            --bg-page: #F8FAFC;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-soft: #E2E8F0;
            --radius-lg: 28px;
            --radius-md: 18px;
            --shadow-card: 0 16px 40px rgba(15, 23, 42, 0.08);
            --shadow-hover: 0 20px 50px rgba(22, 35, 63, 0.16);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
        }

        /* ===== NAVBAR ===== */
        .landing-navbar {
            background: rgba(22, 35, 63, 0.95);
            backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            padding: 18px 0;
            position: sticky;
            top: 0;
            z-index: 1020;
            transition: all 0.25s ease;
        }

        .navbar-brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .navbar-brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #FFFFFF;
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
        }

        .navbar-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .navbar-brand-text strong {
            display: block;
            font-weight: 700;
            font-size: 1.1rem;
            color: #FFFFFF;
            line-height: 1.15;
            letter-spacing: -0.01em;
        }

        .navbar-brand-text span {
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.75);
            letter-spacing: 0.2px;
        }

        .nav-menu-links {
            display: flex;
            align-items: center;
            gap: 24px;
        }

        .nav-menu-links a {
            color: rgba(255, 255, 255, 0.82);
            font-size: 0.88rem;
            font-weight: 500;
            transition: color 0.18s ease;
        }

        .nav-menu-links a:hover {
            color: #FFFFFF;
        }

        .btn-nav-download {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #FFFFFF;
            border-radius: 50px;
            padding: 8px 18px;
            font-size: 0.84rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .btn-nav-download:hover {
            background: rgba(255, 255, 255, 0.22);
            color: #FFFFFF;
            transform: translateY(-1px);
        }

        .btn-nav-login {
            background: #FFFFFF;
            color: var(--primary-navy);
            border: none;
            border-radius: 50px;
            padding: 8px 20px;
            font-size: 0.84rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
            transition: all 0.2s ease;
        }

        .btn-nav-login:hover {
            background: #F1F5F9;
            color: var(--primary-navy-dark);
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.22);
        }

        /* ===== HERO SECTION (BIRU DONGKER CURVED CONTAINER) ===== */
        .hero-section {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-dark) 100%);
            color: #FFFFFF;
            border-radius: 0 0 48px 48px;
            padding: 60px 0 110px 0;
            position: relative;
            overflow: hidden;
        }

        /* Ambient glowing circles */
        .hero-section::before {
            content: '';
            position: absolute;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.15) 0%, transparent 70%);
            top: -120px;
            left: -100px;
            pointer-events: none;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            bottom: -100px;
            right: -80px;
            pointer-events: none;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 50px;
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.20);
            color: #E2E8F0;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 22px;
        }

        .hero-title {
            font-size: 2.85rem;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            color: #FFFFFF;
            margin-bottom: 0;
        }

        .hero-desc {
            font-size: 1rem;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.82);
            font-weight: 400;
        }

        /* Feature Pills Strip */
        .feature-pills-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 36px;
            padding-top: 24px;
            border-top: 1px solid rgba(255, 255, 255, 0.12);
        }

        .feature-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 50px;
            padding: 7px 16px;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.90);
            font-weight: 500;
        }

        /* ===== FLOATING CARDS SHOWCASE (OVERLAPPING HERO) ===== */
        .showcase-wrapper {
            margin-top: -65px;
            position: relative;
            z-index: 10;
            padding-bottom: 40px;
        }

        .showcase-card {
            background: #FFFFFF;
            border-radius: 24px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card);
            padding: 26px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .showcase-card:hover {
            transform: translateY(-6px);
            box-shadow: var(--shadow-hover);
        }

        .showcase-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
        }

        .showcase-badge {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .showcase-badge.primary {
            background: #EFF6FF;
            color: var(--primary-navy);
        }

        .showcase-badge.success {
            background: #ECFDF5;
            color: #059669;
        }

        .showcase-badge.accent {
            background: #F0FDF4;
            color: #16A34A;
        }

        .showcase-card-title {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .showcase-val {
            font-size: 1.55rem;
            font-weight: 800;
            color: var(--text-dark);
            margin: 4px 0 10px 0;
            letter-spacing: -0.01em;
        }

        .showcase-note {
            font-size: 0.78rem;
            color: var(--text-muted);
            line-height: 1.45;
        }

        /* Center APK Card Highlight */
        .apk-hero-card {
            background: linear-gradient(135deg, var(--primary-navy) 0%, #1E3A8A 100%);
            color: #FFFFFF;
            border-radius: 24px;
            padding: 30px 26px;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 20px 50px rgba(22, 35, 63, 0.28);
            border: 1px solid rgba(255, 255, 255, 0.16);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .apk-hero-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 26px 60px rgba(22, 35, 63, 0.36);
        }

        .apk-badge-tag {
            background: rgba(255, 255, 255, 0.18);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50px;
            padding: 4px 12px;
            font-size: 0.72rem;
            font-weight: 600;
            color: #FFFFFF;
        }

        .btn-download-apk-hero {
            background: #FFFFFF;
            color: var(--primary-navy);
            border: none;
            border-radius: 14px;
            padding: 13px 20px;
            font-size: 0.9rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            transition: all 0.2s ease;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        .btn-download-apk-hero:hover {
            background: #F8FAFC;
            color: var(--primary-navy-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.3);
        }

        /* ===== SECTION 2: WHY CHOOSE / STATS ===== */
        .section-why {
            padding: 70px 0 60px 0;
        }

        .section-pill-tag {
            background: #EFF6FF;
            color: var(--primary-navy);
            border: 1px solid #DBEAFE;
            border-radius: 50px;
            padding: 5px 14px;
            font-size: 0.78rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 14px;
        }

        .section-heading {
            font-size: 2.15rem;
            font-weight: 800;
            color: var(--text-dark);
            line-height: 1.25;
            letter-spacing: -0.02em;
        }

        .stat-huge-number {
            font-size: 3.4rem;
            font-weight: 800;
            color: var(--primary-navy);
            line-height: 1;
            letter-spacing: -0.03em;
        }

        .bento-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 22px;
            padding: 30px;
            height: 100%;
            transition: all 0.2s ease;
        }

        .bento-card:hover {
            border-color: #CBD5E1;
            transform: translateY(-4px);
            box-shadow: var(--shadow-card);
        }

        .bento-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            background: #EFF6FF;
            color: var(--primary-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            margin-bottom: 20px;
        }

        .bento-title {
            font-size: 1.12rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .bento-desc {
            font-size: 0.85rem;
            line-height: 1.6;
            color: var(--text-muted);
            margin-bottom: 0;
        }

        /* ===== SECTION 3: STEP WORKFLOW ===== */
        .section-steps {
            background: #FFFFFF;
            border-top: 1px solid var(--border-soft);
            border-bottom: 1px solid var(--border-soft);
            padding: 70px 0;
        }

        .step-item {
            text-align: center;
            padding: 20px 14px;
        }

        .step-circle {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-navy) 0%, #2A3E63 100%);
            color: #FFFFFF;
            font-size: 1.4rem;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 10px 24px rgba(22, 35, 63, 0.22);
        }

        .step-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .step-desc {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.55;
            max-width: 290px;
            margin: 0 auto;
        }

        /* ===== SECTION: BENEFIT MENABUNG ===== */
        .section-benefits {
            padding: 85px 0;
            background-color: #FFFFFF;
        }

        .benefit-card {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 24px;
            padding: 32px 26px;
            height: 100%;
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
        }

        .benefit-card:hover {
            transform: translateY(-5px);
            border-color: #BFDBFE;
            box-shadow: 0 16px 36px rgba(22, 35, 63, 0.08);
        }

        .benefit-icon-wrap {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .benefit-icon-wrap.navy { background: #EFF6FF; color: var(--primary-navy); }
        .benefit-icon-wrap.green { background: #ECFDF5; color: #059669; }
        .benefit-icon-wrap.amber { background: #FFFBEB; color: #D97706; }
        .benefit-icon-wrap.blue { background: #F0F9FF; color: #0284C7; }

        .benefit-card-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 10px;
        }

        .benefit-card-desc {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin: 0;
        }

        /* ===== SECTION: MINI FAQ ===== */
        .section-faq {
            padding: 85px 0 75px 0;
            background-color: var(--bg-main);
        }

        .faq-accordion .accordion-item {
            border: 1px solid var(--border-soft);
            border-radius: 18px !important;
            margin-bottom: 14px;
            background: #FFFFFF;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .faq-accordion .accordion-item:hover {
            border-color: #CBD5E1;
        }

        .faq-accordion .accordion-button {
            font-size: 0.98rem;
            font-weight: 600;
            color: var(--text-dark);
            padding: 20px 24px;
            background: #FFFFFF;
            border: none;
            box-shadow: none !important;
            border-radius: 18px !important;
        }

        .faq-accordion .accordion-button:not(.collapsed) {
            background-color: #F8FAFC;
            color: var(--primary-navy);
            border-bottom: 1px solid var(--border-soft);
            border-bottom-left-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
        }

        .faq-accordion .accordion-button:focus {
            box-shadow: none;
        }

        .faq-accordion .accordion-button::after {
            background-size: 1rem;
        }

        .faq-accordion .accordion-body {
            padding: 20px 24px;
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.7;
            background: #FFFFFF;
        }

        .faq-accordion .accordion-body strong {
            color: var(--text-dark);
        }

        /* ===== SECTION 4: CALL TO ACTION BANNER ===== */
        .section-cta {
            padding: 80px 0;
        }

        .cta-banner {
            background: linear-gradient(135deg, var(--primary-navy) 0%, var(--primary-navy-dark) 100%);
            border-radius: 36px;
            padding: 60px 50px;
            color: #FFFFFF;
            position: relative;
            overflow: hidden;
            box-shadow: 0 24px 60px rgba(22, 35, 63, 0.28);
        }

        .cta-banner::before {
            content: '';
            position: absolute;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, transparent 70%);
            top: -60px;
            right: -60px;
            pointer-events: none;
        }

        .cta-title {
            font-size: 2.2rem;
            font-weight: 800;
            line-height: 1.25;
            margin-bottom: 14px;
        }

        .cta-desc {
            font-size: 0.95rem;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.6;
            margin-bottom: 30px;
            max-width: 580px;
        }

        .btn-cta-white {
            background: #FFFFFF;
            color: var(--primary-navy);
            border: none;
            border-radius: 50px;
            padding: 13px 28px;
            font-size: 0.92rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
            transition: all 0.2s ease;
        }

        .btn-cta-white:hover {
            background: #F1F5F9;
            color: var(--primary-navy-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.3);
        }

        .btn-cta-outline {
            background: transparent;
            color: #FFFFFF;
            border: 1.5px solid rgba(255, 255, 255, 0.35);
            border-radius: 50px;
            padding: 12px 26px;
            font-size: 0.92rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .btn-cta-outline:hover {
            background: rgba(255, 255, 255, 0.12);
            color: #FFFFFF;
            border-color: #FFFFFF;
            transform: translateY(-2px);
        }

        /* ===== FOOTER ===== */
        .landing-footer {
            background: #FFFFFF;
            border-top: 1px solid var(--border-soft);
            padding: 48px 0 30px 0;
        }

        .footer-brand-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
        }

        .footer-logo {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            padding: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .footer-text {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.6;
        }

        .footer-link-col h6 {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 16px;
        }

        .footer-link-col ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-link-col ul li {
            margin-bottom: 10px;
        }

        .footer-link-col ul li a {
            font-size: 0.82rem;
            color: var(--text-muted);
            transition: color 0.18s ease;
        }

        .footer-link-col ul li a:hover {
            color: var(--primary-navy);
        }

        .copyright-bar {
            border-top: 1px solid var(--border-soft);
            padding-top: 24px;
            margin-top: 36px;
            font-size: 0.78rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 991.98px) {
            .hero-title {
                font-size: 2.2rem;
            }
            .hero-section {
                padding: 40px 0 90px 0;
            }
            .cta-banner {
                padding: 40px 28px;
            }
            .cta-title {
                font-size: 1.75rem;
            }
            .nav-menu-links {
                display: none;
            }
        }
    </style>
</head>
<body>

    <!-- ===== TOP NAVBAR ===== -->
    <header class="landing-navbar">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between">
                
                <!-- Brand Logo & School -->
                <a href="/" class="navbar-brand-wrap">
                    <div class="navbar-brand-logo">
                        <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul">
                    </div>
                    <div class="navbar-brand-text">
                        <strong>Bank Mini</strong>
                        <span>SMKN 1 Bantul</span>
                    </div>
                </a>

                <!-- Navigation Links -->
                <nav class="nav-menu-links">
                    <a href="#keunggulan">Keunggulan</a>
                    <a href="#manfaat">Manfaat</a>
                    <a href="#alur">Alur Layanan</a>
                    <a href="#faq">FAQ</a>
                </nav>

                <!-- Action Buttons: Unduh APK & Login Pegawai -->
                <div class="d-flex align-items-center gap-2">
                    <a href="#unduh-apk" class="btn-nav-download" data-bs-toggle="modal" data-bs-target="#modalDownloadApk">
                        <i class="bi bi-android2 fs-6"></i> Unduh APK
                    </a>

                    @if(Auth::check() && in_array(Auth::user()->role, ['Administrator', 'Admin', 'Admin Utama', 'admin_utama', 'Supervisor']))
                        <a href="{{ route('admin.dashboard') }}" class="btn-nav-login">
                            <i class="bi bi-speedometer2"></i> Dashboard Admin
                        </a>
                    @elseif(Auth::check() && Auth::user()->role === 'Teller')
                        <a href="{{ route('teller.dashboard') }}" class="btn-nav-login">
                            <i class="bi bi-speedometer2"></i> Dashboard Teller
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-nav-login">
                            <i class="bi bi-box-arrow-in-right"></i> Login Pegawai
                        </a>
                    @endif
                </div>

            </div>
        </div>
    </header>

    <!-- ===== HERO SECTION (BIRU DONGKER) ===== -->
    <section class="hero-section">
        <div class="container position-relative" style="z-index: 1;">
            
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <div class="hero-badge">
                        <i class="bi bi-shield-check text-info"></i> Sistem Tabungan & Perbankan Sekolah Resmi
                    </div>
                    <h1 class="hero-title">
                        Smart Digital Banking Platform
                    </h1>
                    <div class="mt-2 text-white-50" style="font-size: 1.15rem; font-weight: 500;">
                        SMK Negeri 1 Bantul — Layanan Kas Terintegrasi
                    </div>
                </div>

                <div class="col-lg-5">
                    <p class="hero-desc mb-0">
                        Kelola administrasi tabungan siswa secara transparan, akurat, dan aman. Memudahkan teller melayani transaksi cepat serta nasabah siswa memantau saldo langsung dari genggaman.
                    </p>
                </div>
            </div>

            <!-- Feature Pills Strip -->
            <div class="feature-pills-wrap">
                <div class="feature-pill">
                    <i class="bi bi-lightning-charge-fill text-warning"></i> Setoran & Penarikan Instan
                </div>
                <div class="feature-pill">
                    <i class="bi bi-qr-code-scan text-info"></i> Kartu Siswa & QR Scanner
                </div>
                <div class="feature-pill">
                    <i class="bi bi-journal-text text-success"></i> Jurnal Akuntansi Double-Entry
                </div>
                <div class="feature-pill">
                    <i class="bi bi-safe2-fill text-light"></i> Rekonsiliasi Kas Laci & Brankas
                </div>
            </div>

        </div>
    </section>

    <!-- ===== FLOATING CARDS SHOWCASE (OVERLAPPING HERO) ===== -->
    <section class="showcase-wrapper" id="fitur">
        <div class="container">
            <div class="row g-4 align-items-stretch">
                
                <!-- Card 1: Live Transactions & Balance -->
                <div class="col-md-4">
                    <div class="showcase-card">
                        <div>
                            <div class="showcase-card-header">
                                <div class="showcase-badge primary">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">Real-Time</span>
                            </div>
                            <span class="showcase-card-title">Ringkasan Tabungan</span>
                            <div class="showcase-val">Tabungan Siswa</div>
                            <p class="showcase-note">
                                Saldo tercatat otomatis saat transaksi disetorkan ke loket kasir, dilengkapi proteksi batas penarikan.
                            </p>
                        </div>
                        <div class="pt-3 border-top mt-3 d-flex align-items-center justify-content-between">
                            <span class="small text-muted"><i class="bi bi-shield-lock-fill text-success me-1"></i> Keamanan Kasir</span>
                            <span class="small fw-semibold text-dark">Tervalidasi</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Center Hero APK Card -->
                <div class="col-md-4">
                    <div class="apk-hero-card">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="bi bi-android2 fs-4 text-warning"></i>
                                    <span class="fw-bold fs-6">Aplikasi Nasabah</span>
                                </div>
                                <span class="apk-badge-tag">Versi APK Android</span>
                            </div>
                            <h4 class="fw-bold mb-2">Pantau Saldo dari Smartphone</h4>
                            <p class="small text-white-50 mb-4">
                                Khusus siswa dan wali nasabah SMKN 1 Bantul untuk memantau mutasi tabungan harian dan melihat kartu digital.
                            </p>
                        </div>
                        <div>
                            <button type="button" class="btn-download-apk-hero" data-bs-toggle="modal" data-bs-target="#modalDownloadApk">
                                <i class="bi bi-cloud-arrow-down-fill fs-5"></i> Unduh APK Sekarang
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Rekonsiliasi & Audit -->
                <div class="col-md-4">
                    <div class="showcase-card">
                        <div>
                            <div class="showcase-card-header">
                                <div class="showcase-badge success">
                                    <i class="bi bi-graph-up-arrow"></i>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Balanced</span>
                            </div>
                            <span class="showcase-card-title">Audit Kas & Jurnal</span>
                            <div class="showcase-val">Rekonsiliasi Kas</div>
                            <p class="showcase-note">
                                Kas fisik di laci teller dihitung dan diverifikasi bersama kas sistem sebelum dipindahbukukan ke brankas utama sekolah.
                            </p>
                        </div>
                        <div class="pt-3 border-top mt-3 d-flex align-items-center justify-content-between">
                            <span class="small text-muted"><i class="bi bi-check2-circle text-primary me-1"></i> Akun Kas 101 & 102</span>
                            <span class="small fw-semibold text-success">Seimbang</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ===== SECTION 2: WHY CHOOSE / STATS ===== -->
    <section class="section-why" id="keunggulan">
        <div class="container">
            
            <div class="row align-items-center mb-5 g-4">
                <div class="col-lg-8">
                    <span class="section-pill-tag">✦ Keunggulan Layanan</span>
                    <h2 class="section-heading">
                        Mengapa Siswa & Sekolah Mempercayai Bank Mini?
                    </h2>
                    <p class="text-muted mt-2 mb-0" style="max-width: 620px;">
                        Platform dirancang khusus dengan standar pembukuan akuntansi SMK, mempermudah edukasi literasi keuangan dan keandalan tata kelola kas sekolah.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <div class="stat-huge-number">100%</div>
                    <div class="fw-semibold text-dark">Transparan & Terekonsiliasi</div>
                    <small class="text-muted">Setiap rupiah tercatat dalam buku jurnal</small>
                </div>
            </div>

            <!-- Bento Grid -->
            <div class="row g-4">
                
                <div class="col-md-4">
                    <div class="bento-card">
                        <div class="bento-icon">
                            <i class="bi bi-person-vcard-fill"></i>
                        </div>
                        <h4 class="bento-title">Identitas QR & NIS Terpadu</h4>
                        <p class="bento-desc">
                            Setiap siswa memiliki nomor identitas nasabah dan QR code kartu digital. Teller cukup memindai kartu untuk langsung membuka data nasabah tanpa input manual.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bento-card">
                        <div class="bento-icon">
                            <i class="bi bi-safe-fill"></i>
                        </div>
                        <h4 class="bento-title">Shift Laci & Approval Brankas</h4>
                        <p class="bento-desc">
                            Pengawasan ketat alur kas harian teller. Penutupan laci kasir melewati rekonsiliasi fisik vs sistem dan persetujuan otorisasi admin ke brankas utama.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="bento-card">
                        <div class="bento-icon">
                            <i class="bi bi-file-earmark-bar-graph-fill"></i>
                        </div>
                        <h4 class="bento-title">Laporan & Audit Jurnal Otomatis</h4>
                        <p class="bento-desc">
                            Cetak laporan rekapitulasi bulanan, slip transaksi instan, dan log audit perubahan data pegawai untuk memastikan akuntabilitas sistem tanpa manipulasi.
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ===== SECTION: MANFAAT MENABUNG ===== -->
    <section class="section-benefits" id="manfaat">
        <div class="container">
            
            <div class="row align-items-center mb-5 g-4">
                <div class="col-lg-8">
                    <span class="section-pill-tag">✦ Edukasi Finansial Siswa</span>
                    <h2 class="section-heading">
                        Manfaat Menabung di Bank Mini Sekolah
                    </h2>
                    <p class="text-muted mt-2 mb-0" style="max-width: 640px;">
                        Membangun kebiasaan finansial yang bijak sejak dini, merencanakan kebutuhan studi masa depan, dan memberikan rasa aman bagi siswa maupun orang tua.
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <span class="badge px-3 py-2 rounded-pill fs-7 fw-semibold border" style="background: #EFF6FF; color: var(--primary-navy); border-color: #BFDBFE !important;">
                        <i class="bi bi-mortarboard-fill me-1 text-primary"></i> Karakter Mandiri & Hemat
                    </span>
                </div>
            </div>

            <div class="row g-4">
                
                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrap green">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <h4 class="benefit-card-title">0% Biaya Admin</h4>
                        <p class="benefit-card-desc">
                            Seluruh saldo tabungan tetap utuh 100%. Bebas potongan biaya administrasi bulanan sepeser pun karena berorientasi penuh pada edukasi siswa.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrap amber">
                            <i class="bi bi-piggy-bank-fill"></i>
                        </div>
                        <h4 class="benefit-card-title">Rencana Biaya Studi</h4>
                        <p class="benefit-card-desc">
                            Membiasakan siswa menyisihkan uang saku untuk kebutuhan persiapan PKL, uji sertifikasi kejuruan, wisuda, atau kebutuhan sekolah lainnya.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrap blue">
                            <i class="bi bi-phone-fill"></i>
                        </div>
                        <h4 class="benefit-card-title">Transparansi Digital</h4>
                        <p class="benefit-card-desc">
                            Histori mutasi dan sisa saldo tabungan dapat dicek langsung kapan saja oleh siswa maupun orang tua melalui aplikasi mobile Android Bank Mini.
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-lg-3">
                    <div class="benefit-card">
                        <div class="benefit-icon-wrap navy">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4 class="benefit-card-title">Aman & Terjamin</h4>
                        <p class="benefit-card-desc">
                            Dana tabungan dikelola langsung oleh unit perbankan sekolah dengan pengawasan guru pembimbing dan pencatatan akuntansi ganda (*double-entry*).
                        </p>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ===== SECTION 3: STEP WORKFLOW ===== -->
    <section class="section-steps" id="alur">
        <div class="container">
            
            <div class="text-center mb-5">
                <span class="section-pill-tag">✦ Alur Layanan Mudah</span>
                <h2 class="section-heading">3 Langkah Cepat Bertransaksi</h2>
                <p class="text-muted">Proses mudah mulai dari pendaftaran hingga pengecekan mutasi tabungan.</p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="step-item">
                        <div class="step-circle">1</div>
                        <h4 class="step-title">Aktivasi Akun Siswa</h4>
                        <p class="step-desc">
                            Petugas admin atau teller mengaktivasi data siswa berdasarkan NIS sekolah dan mencetak kartu tabungan nasabah.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-item">
                        <div class="step-circle">2</div>
                        <h4 class="step-title">Transaksi di Loket Teller</h4>
                        <p class="step-desc">
                            Siswa melakukan setor tunai atau penarikan saldo di loket kasir sekolah dengan memindai kartu nasabah.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="step-item">
                        <div class="step-circle">3</div>
                        <h4 class="step-title">Pantau via Smartphone</h4>
                        <p class="step-desc">
                            Nasabah dapat memeriksa histori mutasi dan saldo tabungan secara mandiri melalui aplikasi mobile Android Bank Mini.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ===== SECTION: MINI FAQ ===== -->
    <section class="section-faq" id="faq">
        <div class="container">
            
            <div class="text-center mb-5">
                <span class="section-pill-tag">✦ Pusat Informasi & Bantuan</span>
                <h2 class="section-heading">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                <p class="text-muted" style="max-width: 600px; margin: 0 auto;">
                    Informasi penting seputar tata cara menabung, aktivasi akun, dan penggunaan aplikasi nasabah.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="accordion faq-accordion" id="faqAccordion">
                        
                        <!-- FAQ 1 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="bi bi-question-circle-fill text-primary me-2"></i>
                                    Bagaimana cara siswa membuka rekening tabungan di Bank Mini?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Siswa cukup datang ke <strong>Loket Kasir Bank Mini SMKN 1 Bantul</strong> dengan membawa Kartu Siswa atau menyebutkan Nomor Induk Siswa (NIS). Petugas teller akan langsung mengaktivasi akun tabungan dan mencetak kartu nasabah ber-barcode dalam hitungan menit tanpa biaya pendaftaran.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="bi bi-question-circle-fill text-primary me-2"></i>
                                    Berapa setoran awal minimal untuk mulai menabung?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Setoran awal sangat terjangkau, mulai dari <strong>Rp 10.000</strong>. Siswa bebas menyetor tabungan harian atau mingguan sesuai nominal uang saku yang disisihkan, tanpa ada batasan minimal yang memberatkan.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <i class="bi bi-question-circle-fill text-primary me-2"></i>
                                    Apakah ada biaya administrasi bulanan atau saldo hangus?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    <strong>Sama sekali tidak ada.</strong> Bank Mini SMKN 1 Bantul berorientasi penuh pada edukasi literasi keuangan dan pembelajaran kejuruan siswa. Saldo tabungan Anda tetap utuh 100% tanpa potongan biaya administrasi bulanan sepeser pun.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 4 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="bi bi-question-circle-fill text-primary me-2"></i>
                                    Bagaimana cara nasabah memeriksa saldo dan riwayat mutasi tabungan?
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Siswa dan orang tua dapat mengunduh <strong>Aplikasi Mobile Bank Mini (Android)</strong> yang tersedia di halaman ini. Cukup buka aplikasi dan masukkan nomor rekening atau scan barcode kartu nasabah untuk melihat saldo terkini serta histori seluruh transaksi secara real-time.
                                </div>
                            </div>
                        </div>

                        <!-- FAQ 5 -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingFive">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    <i class="bi bi-question-circle-fill text-primary me-2"></i>
                                    Bagaimana jika kartu tabungan fisik hilang atau rusak?
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Tidak perlu panik, saldo tabungan Anda tetap aman di database sistem perbankan sekolah. Siswa dapat langsung melapor ke petugas teller dengan membawa NIS untuk verifikasi identitas, dan teller akan mencetak ulang kartu tabungan baru.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ===== SECTION 4: CALL TO ACTION BANNER ===== -->
    <section class="section-cta" id="unduh-apk">
        <div class="container">
            <div class="cta-banner">
                <div class="row align-items-center g-4">
                    <div class="col-lg-8">
                        <h2 class="cta-title">
                            Akses Tabungan Sekolah Langsung dari Genggamanmu
                        </h2>
                        <p class="cta-desc">
                            Unduh aplikasi mobile Bank Mini SMKN 1 Bantul untuk smartphone Android. Pantau mutasi keuangan, cek saldo real-time, dan simpan barcode kartu nasabah secara praktis.
                        </p>
                        <div class="d-flex gap-3 flex-wrap align-items-center">
                            <button type="button" class="btn-cta-white" data-bs-toggle="modal" data-bs-target="#modalDownloadApk">
                                <i class="bi bi-android2 fs-5"></i> Unduh APK Nasabah (Android)
                            </button>
                            <a href="{{ route('login') }}" class="btn-cta-outline">
                                <i class="bi bi-box-arrow-in-right"></i> Login Pegawai
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-4 text-center">
                        <div class="p-4 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-25 d-inline-block">
                            <div class="text-white-50 small mb-2">Didukung oleh:</div>
                            <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul" style="width: 100px; height: 100px; object-fit: contain; filter: drop-shadow(0 8px 16px rgba(0,0,0,0.3));">
                            <div class="fw-bold mt-2 text-white small">SMK Negeri 1 Bantul</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer class="landing-footer">
        <div class="container">
            <div class="row g-4">
                
                <div class="col-lg-5">
                    <div class="footer-brand-wrap">
                        <div class="footer-logo">
                            <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul">
                        </div>
                        <div class="navbar-brand-text">
                            <strong style="color: var(--primary-navy);">Bank Mini</strong>
                            <span style="color: var(--text-muted);">SMKN 1 Bantul</span>
                        </div>
                    </div>
                    <p class="footer-text mb-3" style="max-width: 380px;">
                        Sistem informasi perbankan mini dan pembukuan kas sekolah berbasis web dan aplikasi mobile untuk SMK Negeri 1 Bantul.
                    </p>
                    <div class="small text-muted">
                        <i class="bi bi-geo-alt-fill text-danger me-1"></i> Jl. Parangtritis Km 11, Sabdodadi, Bantul, D.I. Yogyakarta 55702
                    </div>
                </div>

                <div class="col-6 col-lg-3 footer-link-col">
                    <h6>Menu Navigasi</h6>
                    <ul>
                        <li><a href="#keunggulan">Keunggulan Sistem</a></li>
                        <li><a href="#manfaat">Manfaat Menabung</a></li>
                        <li><a href="#alur">Alur Transaksi</a></li>
                        <li><a href="#faq">Pertanyaan (FAQ)</a></li>
                        <li><a href="#unduh-apk">Unduh Aplikasi Mobile</a></li>
                    </ul>
                </div>

                <div class="col-6 col-lg-4 footer-link-col">
                    <h6>Akses Pengguna</h6>
                    <ul>
                        <li><a href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-1"></i> Login Pegawai (Admin & Teller)</a></li>
                        <li><a href="{{ route('password.request') }}"><i class="bi bi-key me-1"></i> Lupa Kata Sandi Pegawai</a></li>
                        <li><a href="#" data-bs-toggle="modal" data-bs-target="#modalDownloadApk"><i class="bi bi-download me-1"></i> Unduh Berkas Installer APK</a></li>
                    </ul>
                </div>

            </div>

            <div class="copyright-bar">
                <div>
                    © {{ date('Y') }} Bank Mini SMKN 1 Bantul. Seluruh hak cipta dilindungi.
                </div>
                <div>
                    <span class="badge bg-light text-secondary border">Platform Digital Keuangan Sekolah</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- ===== MODAL UNDUH APK ===== -->
    <div class="modal fade" id="modalDownloadApk" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 60px rgba(22, 35, 63, 0.25);">
                <div class="modal-header border-0 pb-0 justify-content-between pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #EFF6FF; color: var(--primary-navy);">
                            <i class="bi bi-android2 fs-4"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-dark m-0">Unduh Aplikasi Nasabah</h5>
                            <small class="text-muted">Aplikasi Mobile Siswa (Android)</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body py-4 px-4">
                    <div class="p-3 rounded-3 mb-3" style="background: #F8FAFC; border: 1.5px solid #E2E8F0;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Nama Berkas:</span>
                            <span class="small fw-bold text-dark">BankMini-Nasabah.apk</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Ukuran Berkas:</span>
                            <span class="small fw-bold text-dark">~50.0 MB</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Versi Rilis:</span>
                            <span class="small fw-bold text-success"><i class="bi bi-patch-check-fill me-1"></i>v1.0.0 (Release)</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Kompatibilitas:</span>
                            <span class="small fw-semibold text-dark">Android 8.0 (Oreo) ke atas</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Sasaran Pengguna:</span>
                            <span class="small fw-semibold text-primary">Siswa Nasabah SMKN 1 Bantul</span>
                        </div>
                    </div>

                    <div class="alert alert-info py-2 px-3 small rounded-3 mb-4 d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill text-info flex-shrink-0 mt-1"></i>
                        <div>
                            Setelah berkas APK selesai diunduh, buka berkas di smartphone Anda dan izinkan <strong>"Instal dari Sumber Tidak Dikenal"</strong> pada pengaturan keamanan perangkat.
                        </div>
                    </div>

                    <a href="{{ route('download.apk') }}" class="btn w-100 py-3 fw-bold text-white shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: var(--primary-navy); border-radius: 14px;" download="BankMini-Nasabah.apk">
                        <i class="bi bi-download fs-5"></i> Unduh Berkas APK Sekarang
                    </a>
                </div>

                <div class="modal-footer border-0 pt-0 pb-4 px-4 justify-content-center">
                    <small class="text-muted">Butuh bantuan akun? Hubungi loket teller Bank Mini di sekolah.</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>

