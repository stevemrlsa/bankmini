<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Buku Tabungan Digital — Bank Mini SMKN 1 Bantul</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-smkn1bantul.png') }}">

    <!-- PWA & Mobile Web App Meta Tags (Khusus iOS Safari & Android Chrome) -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Bank Mini">
    <meta name="theme-color" content="#16233F">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-smkn1bantul.png') }}">
    <link rel="manifest" href="data:application/manifest+json;charset=utf-8,%7B%22name%22%3A%22Bank%20Mini%20SMKN%201%20Bantul%22%2C%22short_name%22%3A%22Bank%20Mini%22%2C%22start_url%22%3A%22%2Fnasabah%2Fdashboard%22%2C%22display%22%3A%22standalone%22%2C%22background_color%22%3A%22%23F8FAFC%22%2C%22theme_color%22%3A%22%2316233F%22%7D">

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
            padding-bottom: 40px;
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
            flex-shrink: 1;
        }

        .brand-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
            flex-shrink: 0;
        }

        .brand-text {
            min-width: 0;
        }

        .brand-text strong {
            display: block;
            font-size: 1.02rem;
            font-weight: 700;
            color: var(--primary-navy);
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .brand-text span {
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: block;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #F1F5F9;
            border: 1px solid #E2E8F0;
            padding: 4px 12px 4px 5px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-dark);
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
            display: flex;
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

        /* ===== Main Container ===== */
        .portal-main {
            max-width: 1100px;
            margin: 0 auto;
            padding: 22px 18px;
            width: 100%;
            flex-grow: 1;
        }

        /* ===== iOS Safari PWA Banner ===== */
        .pwa-ios-banner {
            background: linear-gradient(135deg, #16233F 0%, #1E3A8A 100%);
            border-radius: var(--radius-lg);
            padding: 14px 18px;
            color: #FFFFFF;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(22, 35, 63, 0.15);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .pwa-ios-banner.hidden {
            display: none !important;
        }

        .pwa-banner-icon {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.12);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .pwa-banner-text h6 {
            font-weight: 700;
            font-size: 0.9rem;
            margin-bottom: 2px;
        }

        .pwa-banner-text p {
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.85);
            margin: 0;
            line-height: 1.38;
        }

        .btn-close-pwa {
            background: transparent;
            border: none;
            color: rgba(255, 255, 255, 0.7);
            font-size: 1.2rem;
            padding: 4px;
            cursor: pointer;
            transition: color 0.15s ease;
            flex-shrink: 0;
        }

        .btn-close-pwa:hover {
            color: #FFFFFF;
        }

        /* ===== Kartu Debit Digital (SimPel) ===== */
        .debit-card-simpel {
            width: 100%;
            max-width: 430px;
            background: linear-gradient(135deg, #0A1426 0%, #16233F 55%, #1E3A8A 100%);
            border-radius: 22px;
            padding: 22px 24px;
            color: #FFFFFF;
            position: relative;
            overflow: hidden;
            box-shadow: 0 18px 45px rgba(10, 20, 38, 0.28), 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 1.5px solid rgba(255, 255, 255, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 16px;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            margin: 0 auto;
        }

        .debit-card-simpel:hover {
            transform: translateY(-3px);
            box-shadow: 0 24px 50px rgba(10, 20, 38, 0.35);
        }

        /* Card background pattern */
        .debit-card-simpel::before {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(14, 165, 233, 0.18) 0%, transparent 70%);
            top: -60px;
            right: -60px;
            pointer-events: none;
        }

        .debit-card-simpel::after {
            content: '';
            position: absolute;
            width: 220px;
            height: 220px;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, transparent 70%);
            bottom: -50px;
            left: -50px;
            pointer-events: none;
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 2;
            gap: 8px;
        }

        .card-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
        }

        .card-brand-logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
            flex-shrink: 0;
        }

        .card-brand-text {
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.6px;
            line-height: 1.2;
            text-transform: uppercase;
        }

        .card-simpel-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            color: #F8FAFC;
            flex-shrink: 0;
        }

        .card-middle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 2;
        }

        .chip-graphic {
            width: 40px;
            height: 29px;
            border-radius: 6px;
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            position: relative;
            box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.4);
        }

        .chip-graphic::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: rgba(0, 0, 0, 0.2);
        }

        .chip-graphic::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 1px;
            background: rgba(0, 0, 0, 0.2);
        }

        .nfc-icon {
            font-size: 1.25rem;
            transform: rotate(90deg);
            color: rgba(255, 255, 255, 0.7);
        }

        .card-number-row {
            font-family: 'Courier New', Courier, monospace;
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 2px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 2;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
            background: rgba(255, 255, 255, 0.06);
            padding: 6px 12px;
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .btn-copy-acc {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: #FFFFFF;
            width: 26px;
            height: 26px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .btn-copy-acc:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .card-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            position: relative;
            z-index: 2;
            gap: 10px;
        }

        .card-holder-name {
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .card-holder-meta {
            font-size: 0.68rem;
            color: rgba(255, 255, 255, 0.82);
            letter-spacing: 0.2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px;
        }

        .card-status-pill {
            background: rgba(16, 185, 129, 0.25);
            border: 1px solid rgba(16, 185, 129, 0.5);
            padding: 3px 10px;
            border-radius: 50px;
            font-size: 0.66rem;
            font-weight: 700;
            color: #A7F3D0;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            flex-shrink: 0;
        }

        .status-pulse {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #10B981;
            box-shadow: 0 0 8px #10B981;
        }

        /* ===== Saldo & Action Card ===== */
        .balance-card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 22px 24px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .balance-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 6px;
        }

        .balance-label {
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
        }

        .btn-toggle-eye {
            border: 1px solid var(--border-soft);
            background: #F8FAFC;
            color: var(--text-muted);
            padding: 4px 10px;
            border-radius: 50px;
            font-size: 0.74rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-toggle-eye:hover {
            background: #F1F5F9;
            color: var(--text-dark);
        }

        .balance-nominal {
            font-size: 2rem;
            font-weight: 800;
            color: var(--primary-navy);
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin-bottom: 14px;
            word-break: break-word;
        }

        .balance-stats-pills {
            display: flex;
            gap: 10px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .stat-chip {
            flex: 1 1 calc(50% - 5px);
            padding: 8px 12px;
            border-radius: 12px;
            background: #F8FAFC;
            border: 1px solid #EEF2F6;
            min-width: 120px;
        }

        .stat-chip span {
            display: block;
            font-size: 0.72rem;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .stat-chip strong {
            font-size: 0.86rem;
            font-weight: 700;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-chip.deposit strong {
            color: #10B981;
        }

        .stat-chip.withdraw strong {
            color: #EF4444;
        }

        /* ===== Quick Action Grid ===== */
        .quick-actions-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
        }

        .quick-act-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 4px;
            border-radius: 14px;
            background: #F8FAFC;
            border: 1px solid var(--border-soft);
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.18s ease;
            text-align: center;
            cursor: pointer;
        }

        .quick-act-btn:hover {
            background: #EFF6FF;
            border-color: #BFDBFE;
            color: var(--primary-navy);
            transform: translateY(-2px);
            box-shadow: 0 6px 14px rgba(22, 35, 63, 0.06);
        }

        .quick-act-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 4px;
        }

        .quick-act-label {
            font-size: 0.72rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }

        /* ===== Savings Goal Card (Celengan Impian) ===== */
        .goal-card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 20px 24px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            margin-bottom: 22px;
        }

        .goal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            gap: 10px;
        }

        .goal-title {
            font-size: 0.92rem;
            font-weight: 700;
            color: var(--primary-navy);
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
        }

        .goal-title-text {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .btn-edit-goal {
            font-size: 0.76rem;
            color: var(--accent-cyan);
            background: transparent;
            border: none;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .goal-progress-wrap {
            position: relative;
            background: #F1F5F9;
            height: 12px;
            border-radius: 50px;
            overflow: hidden;
            margin-bottom: 8px;
        }

        .goal-progress-bar {
            height: 100%;
            border-radius: 50px;
            background: linear-gradient(90deg, #10B981 0%, #059669 100%);
            transition: width 0.6s ease;
        }

        .goal-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.78rem;
            color: var(--text-muted);
            gap: 8px;
        }

        /* ===== Mutasi & Transaksi Section ===== */
        .history-card {
            background: #FFFFFF;
            border-radius: 22px;
            padding: 22px 24px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.04);
            margin-bottom: 22px;
        }

        .history-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .history-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--primary-navy);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-nav {
            display: flex;
            gap: 4px;
            background: #F1F5F9;
            padding: 3px;
            border-radius: 50px;
        }

        .filter-btn {
            border: none;
            background: transparent;
            padding: 5px 12px;
            border-radius: 50px;
            font-size: 0.76rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .filter-btn.active {
            background: #FFFFFF;
            color: var(--primary-navy);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
        }

        .trx-list-wrap {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .trx-item-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 13px 16px;
            border-radius: 16px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            transition: all 0.18s ease;
            gap: 12px;
        }

        .trx-item-card:hover {
            border-color: #CBD5E1;
            background: #F8FAFC;
        }

        .trx-item-left {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
            flex: 1 1 auto;
        }

        .trx-icon-circle {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .trx-icon-circle.deposit {
            background: #D1FAE5;
            color: #059669;
        }

        .trx-icon-circle.withdraw {
            background: #FEE2E2;
            color: #DC2626;
        }

        .trx-info {
            min-width: 0;
        }

        .trx-info h6 {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--text-dark);
            margin-bottom: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .trx-info small {
            font-size: 0.72rem;
            color: var(--text-muted);
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .trx-item-right {
            text-align: end;
            flex-shrink: 0;
        }

        .trx-amount {
            font-weight: 700;
            font-size: 0.92rem;
            display: block;
            margin-bottom: 2px;
            white-space: nowrap;
        }

        .trx-amount.deposit {
            color: #059669;
        }

        .trx-amount.withdraw {
            color: #DC2626;
        }

        .trx-balance-after {
            font-size: 0.7rem;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .trx-empty {
            text-align: center;
            padding: 36px 20px;
            color: var(--text-muted);
        }

        .trx-empty i {
            font-size: 2.6rem;
            color: #CBD5E1;
            margin-bottom: 8px;
            display: block;
        }

        /* ===== Info Loket & Keamanan ===== */
        .info-loket-card {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 18px 22px;
            border: 1px solid var(--border-soft);
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.03);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .info-loket-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: #EFF6FF;
            color: #1E3A8A;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .info-loket-text h6 {
            font-weight: 700;
            font-size: 0.9rem;
            color: var(--primary-navy);
            margin-bottom: 2px;
        }

        .info-loket-text p {
            font-size: 0.76rem;
            color: var(--text-muted);
            margin: 0;
            line-height: 1.42;
        }

        /* ===== Modal QR Styling ===== */
        .qr-card-display {
            background: #FFFFFF;
            border-radius: 20px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            border: 1.5px solid #E2E8F0;
        }

        .qr-svg-wrap {
            display: inline-block;
            padding: 12px;
            background: #FFFFFF;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            margin-bottom: 14px;
            max-width: 100%;
        }

        .qr-svg-wrap svg {
            width: 190px !important;
            height: 190px !important;
            max-width: 100% !important;
            display: block;
        }

        /* ===== RESPONSIVE MEDIA QUERIES (Khusus Layar HP / Mobile) ===== */
        @media (max-width: 768px) {
            body {
                padding-bottom: calc(24px + env(safe-area-inset-bottom, 0px));
            }

            .portal-main {
                padding: 16px 14px;
            }

            .debit-card-simpel {
                max-width: 100%;
                padding: 18px 20px;
                border-radius: 20px;
                gap: 14px;
            }

            .card-number-row {
                font-size: 1.05rem;
                letter-spacing: 1.6px;
                padding: 5px 10px;
            }

            .balance-card {
                padding: 18px 20px;
                border-radius: 20px;
            }

            .balance-nominal {
                font-size: 1.75rem;
            }

            .quick-actions-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 6px;
            }
        }

        @media (max-width: 576px) {
            .header-container {
                padding: 10px 14px;
            }

            .brand-logo {
                width: 34px;
                height: 34px;
            }

            .brand-text strong {
                font-size: 0.92rem;
            }

            .brand-text span {
                font-size: 0.66rem;
            }

            .btn-header-action {
                width: 35px;
                height: 35px;
                font-size: 0.88rem;
            }

            .pwa-ios-banner {
                padding: 12px 14px;
                flex-direction: row;
                align-items: flex-start;
                gap: 10px;
            }

            .pwa-banner-icon {
                width: 36px;
                height: 36px;
                font-size: 1.15rem;
            }

            .pwa-banner-text h6 {
                font-size: 0.84rem;
            }

            .pwa-banner-text p {
                font-size: 0.74rem;
            }

            .debit-card-simpel {
                padding: 16px 16px;
                border-radius: 18px;
                gap: 12px;
            }

            .card-brand-logo {
                width: 28px;
                height: 28px;
            }

            .card-brand-text {
                font-size: 0.65rem;
            }

            .card-simpel-badge {
                font-size: 0.62rem;
                padding: 2px 7px;
            }

            .chip-graphic {
                width: 34px;
                height: 25px;
            }

            .nfc-icon {
                font-size: 1.05rem;
            }

            .card-number-row {
                font-size: 0.96rem;
                letter-spacing: 1.2px;
                padding: 4px 8px;
            }

            .card-holder-name {
                font-size: 0.78rem;
                max-width: 170px;
            }

            .card-holder-meta {
                font-size: 0.65rem;
                max-width: 170px;
            }

            .card-status-pill {
                font-size: 0.6rem;
                padding: 2px 6px;
            }

            .balance-card {
                padding: 16px 16px;
                border-radius: 18px;
            }

            .balance-nominal {
                font-size: 1.6rem;
                margin-bottom: 10px;
            }

            .balance-stats-pills {
                gap: 8px;
                margin-bottom: 14px;
            }

            .stat-chip {
                padding: 7px 10px;
            }

            .stat-chip span {
                font-size: 0.68rem;
            }

            .stat-chip strong {
                font-size: 0.8rem;
            }

            .quick-act-btn {
                padding: 8px 3px;
                border-radius: 12px;
            }

            .quick-act-icon {
                width: 32px;
                height: 32px;
                font-size: 0.95rem;
                margin-bottom: 3px;
            }

            .quick-act-label {
                font-size: 0.66rem;
            }

            .goal-card {
                padding: 15px 16px;
                border-radius: 18px;
            }

            .goal-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .btn-edit-goal {
                align-self: flex-end;
                margin-top: -24px;
                font-size: 0.72rem;
            }

            .goal-meta {
                flex-direction: column;
                gap: 3px;
                font-size: 0.72rem;
            }

            .history-card {
                padding: 16px 14px;
                border-radius: 18px;
            }

            .history-header {
                flex-direction: column;
                align-items: stretch;
                gap: 10px;
            }

            .history-title {
                font-size: 0.96rem;
            }

            .filter-nav {
                width: 100%;
                display: flex;
                overflow-x: auto;
            }

            .filter-btn {
                flex: 1 1 auto;
                text-align: center;
                padding: 5px 6px;
                font-size: 0.72rem;
            }

            .trx-item-card {
                padding: 11px 12px;
                gap: 8px;
            }

            .trx-item-left {
                gap: 10px;
            }

            .trx-icon-circle {
                width: 34px;
                height: 34px;
                font-size: 1.05rem;
                border-radius: 10px;
            }

            .trx-info h6 {
                font-size: 0.8rem;
            }

            .trx-info small {
                font-size: 0.66rem;
            }

            .trx-amount {
                font-size: 0.84rem;
            }

            .trx-balance-after {
                font-size: 0.66rem;
            }

            .info-loket-card {
                padding: 14px 14px;
                border-radius: 16px;
                gap: 10px;
            }

            .info-loket-icon {
                width: 38px;
                height: 38px;
                font-size: 1.15rem;
            }

            .info-loket-text h6 {
                font-size: 0.84rem;
            }

            .info-loket-text p {
                font-size: 0.72rem;
            }
        }
    </style>
</head>
<body>

    <!-- ===== TOPBAR HEADER ===== -->
    <header class="portal-header">
        <div class="header-container">
            <a href="{{ route('saver.dashboard') }}" class="brand-wrap">
                <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul" class="brand-logo">
                <div class="brand-text">
                    <strong>Bank Mini</strong>
                    <span>SMKN 1 Bantul — SimPel</span>
                </div>
            </a>

            <div class="header-actions">
                <!-- 1. Nama Nasabah (Klik untuk Masuk ke Halaman Profil) -->
                <a href="{{ route('saver.profile') }}" class="user-pill text-decoration-none" title="Lihat Profil & Keamanan Akun">
                    <div class="user-avatar-circle">
                        {{ strtoupper(substr($saver->student_name, 0, 1)) }}
                    </div>
                    <span>{{ explode(' ', $saver->student_name)[0] }}</span>
                </a>

                <!-- 2. Tombol Logout -->
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

        <!-- Alert Notifikasi Flash -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-3 border-0 shadow-sm rounded-4" role="alert" style="background-color: #D1FAE5; color: #065F46; font-size: 0.84rem;">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-3 border-0 shadow-sm rounded-4" role="alert" style="background-color: #FEE2E2; color: #991B1B; font-size: 0.84rem;">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Banner Khusus Pengguna iPhone (PWA Tip) -->
        <div class="pwa-ios-banner" id="pwaIosBanner">
            <div class="d-flex align-items-center gap-2 min-w-0">
                <div class="pwa-banner-icon">
                    <i class="bi bi-apple"></i>
                </div>
                <div class="pwa-banner-text min-w-0">
                    <h6>Pasang Bank Mini di Layar Utama iPhone / iPad</h6>
                    <p>Tekan tombol <strong>Bagikan (Share <i class="bi bi-box-arrow-up"></i>)</strong> di Safari lalu pilih <strong>"Tambahkan ke Layar Utama" (Add to Home Screen)</strong> untuk akses cepat tanpa App Store!</p>
                </div>
            </div>
            <button type="button" class="btn-close-pwa" id="btnClosePwa" title="Tutup panduan">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- ROW 1: KARTU DEBIT DIGITAL SIMPEL & CEK SALDO MANDIRI -->
        <div class="row g-3 g-md-4 mb-3 mb-md-4 align-items-stretch">
            
            <!-- Kolom Kiri: Kartu Debit Digital Resmi -->
            <div class="col-lg-5 col-md-6 d-flex">
                <div class="debit-card-simpel">
                    <div class="card-top">
                        <div class="card-brand">
                            <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul" class="card-brand-logo">
                            <div class="card-brand-text">
                                Bank Mini<br>SMKN 1 Bantul
                            </div>
                        </div>
                        <span class="card-simpel-badge">SimPel</span>
                    </div>

                    <div class="card-middle">
                        <div class="chip-graphic"></div>
                        <i class="bi bi-wifi nfc-icon"></i>
                    </div>

                    <div class="card-number-row">
                        <span id="cardAccText">{{ chunk_split($saver->account_number ?? '20260000', 4, ' ') }}</span>
                        <button type="button" class="btn-copy-acc" onclick="copyAccountNumber('{{ $saver->account_number }}')" title="Salin No. Rekening">
                            <i class="bi bi-copy"></i>
                        </button>
                    </div>

                    <div class="card-bottom">
                        <div class="min-w-0">
                            <div class="card-holder-name">{{ $saver->student_name }}</div>
                            <div class="card-holder-meta">NIS: {{ $saver->student_id }} • {{ $saver->grade_class ?? 'Siswa' }}</div>
                        </div>
                        <div>
                            <span class="card-status-pill">
                                <span class="status-pulse"></span> AKTIF
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Saldo Real-Time & Menu Aksi Cepat -->
            <div class="col-lg-7 col-md-6 d-flex">
                <div class="balance-card w-100">
                    <div>
                        <div class="balance-header">
                            <span class="balance-label"><i class="bi bi-wallet2 me-1"></i> Saldo Tabungan SimPel</span>
                            <button type="button" class="btn-toggle-eye" id="btnToggleBalance">
                                <i class="bi bi-eye-slash" id="eyeIcon"></i>
                                <span id="eyeText">Sembunyikan</span>
                            </button>
                        </div>

                        <!-- Nominal Saldo Real-Time dengan Fitur Sensor Nominal -->
                        <div class="balance-nominal" id="balanceDisplay">
                            Rp {{ number_format($saver->saldo, 0, ',', '.') }}
                        </div>

                        <!-- Ringkasan Akumulasi Masuk & Keluar -->
                        <div class="balance-stats-pills">
                            <div class="stat-chip deposit">
                                <span><i class="bi bi-arrow-down-left"></i> Setoran Masuk</span>
                                <strong>+ Rp {{ number_format($totalDeposits, 0, ',', '.') }}</strong>
                            </div>
                            <div class="stat-chip withdraw">
                                <span><i class="bi bi-arrow-up-right"></i> Penarikan Kas</span>
                                <strong>- Rp {{ number_format($totalWithdrawals, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Grid Aksi Cepat Siswa -->
                    <div class="quick-actions-grid">
                        <button type="button" class="quick-act-btn" data-bs-toggle="modal" data-bs-target="#modalQrCode">
                            <div class="quick-act-icon" style="background: #EFF6FF; color: #1E3A8A;">
                                <i class="bi bi-qr-code-scan"></i>
                            </div>
                            <span class="quick-act-label">QR Loket</span>
                        </button>

                        <a href="#riwayatMutasi" class="quick-act-btn">
                            <div class="quick-act-icon" style="background: #ECFDF5; color: #059669;">
                                <i class="bi bi-journal-text"></i>
                            </div>
                            <span class="quick-act-label">Mutasi</span>
                        </a>

                        <a href="{{ route('saver.statement') }}" target="_blank" class="quick-act-btn">
                            <div class="quick-act-icon" style="background: #FEF3C7; color: #D97706;">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </div>
                            <span class="quick-act-label">Rek. Koran</span>
                        </a>

                        <a href="{{ route('saver.profile') }}" class="quick-act-btn">
                            <div class="quick-act-icon" style="background: #F3E8FF; color: #7E22CE;">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <span class="quick-act-label">Profil &amp; PIN</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- ROW 2: CELENGAN IMPIAN & TARGET MENABUNG SISWA -->
        @php
            $targetNominal = (float)($savingsGoal['target'] ?? 500000);
            $targetNominal = $targetNominal > 0 ? $targetNominal : 500000;
            $percentGoal = min(100, round(($saver->saldo / $targetNominal) * 100));
        @endphp
        <div class="goal-card">
            <div class="goal-header">
                <div class="goal-title">
                    <i class="bi bi-piggy-bank-fill text-warning fs-5"></i>
                    <span class="goal-title-text">Target: <strong>{{ $savingsGoal['title'] ?? 'Tabungan Masa Depan' }}</strong></span>
                </div>
                <button type="button" class="btn-edit-goal" data-bs-toggle="modal" data-bs-target="#modalGoal">
                    <i class="bi bi-pencil-square me-1"></i> Ubah Target
                </button>
            </div>

            <div class="goal-progress-wrap">
                <div class="goal-progress-bar" style="width: {{ $percentGoal }}%;"></div>
            </div>

            <div class="goal-meta">
                <span>Terkumpul: <strong>Rp {{ number_format($saver->saldo, 0, ',', '.') }}</strong></span>
                <span>Progres: <strong class="text-success">{{ $percentGoal }}%</strong></span>
                <span>Target: <strong>Rp {{ number_format($targetNominal, 0, ',', '.') }}</strong></span>
            </div>
        </div>

        <!-- ROW 3: BUKU TABUNGAN DIGITAL & RIWAYAT MUTASI -->
        <div class="history-card" id="riwayatMutasi">
            <div class="history-header">
                <div>
                    <h5 class="history-title mb-1">
                        <i class="bi bi-journal-check text-primary"></i> Buku Tabungan Digital & Mutasi
                    </h5>
                    <small class="text-muted">Catatan resmi seluruh setoran dan penarikan yang terverifikasi kasir sekolah</small>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="filter-nav">
                        <button type="button" class="filter-btn active" onclick="filterTransactions('all')">Semua</button>
                        <button type="button" class="filter-btn" onclick="filterTransactions('Deposit')">Setoran (+)</button>
                        <button type="button" class="filter-btn" onclick="filterTransactions('Withdrawal')">Penarikan (-)</button>
                    </div>

                    <a href="{{ route('saver.statement') }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 d-none d-sm-inline-flex align-items-center">
                        <i class="bi bi-printer me-1"></i> Cetak Rekening Koran
                    </a>
                </div>
            </div>

            <!-- List Transaksi -->
            <div class="trx-list-wrap" id="trxListContainer">
                @forelse($transactions as $trx)
                    @php
                        $isDeposit = in_array($trx->transaction_type, ['Deposit', 'deposit', 'setor', 'setoran']);
                        $tellerName = $trx->user->officer_name ?? $trx->user->username ?? 'Teller Loket';
                    @endphp
                    <div class="trx-item-card" data-type="{{ $isDeposit ? 'Deposit' : 'Withdrawal' }}">
                        <div class="trx-item-left">
                            <div class="trx-icon-circle {{ $isDeposit ? 'deposit' : 'withdraw' }}">
                                <i class="bi {{ $isDeposit ? 'bi-arrow-down-left' : 'bi-arrow-up-right' }}"></i>
                            </div>
                            <div class="trx-info">
                                <h6>{{ $isDeposit ? 'Setoran Tabungan di Loket' : 'Penarikan Saldo Kas' }}</h6>
                                <small>
                                    <i class="bi bi-calendar3 me-1"></i>{{ \Carbon\Carbon::parse($trx->transaction_date ?? $trx->created_at)->translatedFormat('d M Y, H:i') }} WIB
                                    • Kasir: {{ $tellerName }}
                                </small>
                            </div>
                        </div>

                        <div class="trx-item-right">
                            <span class="trx-amount {{ $isDeposit ? 'deposit' : 'withdraw' }}">
                                {{ $isDeposit ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </span>
                            <span class="trx-balance-after">
                                Saldo: Rp {{ number_format($trx->running_balance ?? $saver->saldo, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="trx-empty">
                        <i class="bi bi-inbox"></i>
                        <h6 class="fw-bold mb-1">Belum Ada Riwayat Transaksi</h6>
                        <small>Silakan kunjungi loket Bank Mini SMKN 1 Bantul untuk melakukan setoran tabungan pertama Anda.</small>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ROW 4: INFORMASI JADWAL LOKET & KEAMANAN ZERO FRAUD -->
        <div class="info-loket-card">
            <div class="info-loket-icon">
                <i class="bi bi-clock-history"></i>
            </div>
            <div class="info-loket-text">
                <h6>Jam Pelayanan Loket Kasir Bank Mini SMKN 1 Bantul</h6>
                <p>
                    Buka setiap hari sekolah: <strong>Senin s.d. Kamis: 08.00 – 14.30 WIB</strong> | <strong>Jumat: 08.00 – 11.30 WIB</strong> di Ruang Laboratorium Akuntansi.
                    Seluruh penarikan saldo wajib divalidasi langsung oleh Teller di loket untuk menjamin keamanan tabungan siswa.
                </p>
            </div>
        </div>

    </main>

    <!-- ===== MODAL QR CODE SISWA ===== -->
    <div class="modal fade" id="modalQrCode" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
                <div class="modal-header border-0 pb-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-qr-code text-primary fs-5"></i> QR Code Rekening Siswa
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3 p-md-4">
                    <div class="qr-card-display mb-3">
                        <div class="qr-svg-wrap">
                            @if(class_exists(\SimpleSoftwareIO\QrCode\Facades\QrCode::class))
                                {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(190)->generate($saver->account_number ?? $saver->student_id) !!}
                            @else
                                <div id="qrcodeCanvas"></div>
                            @endif
                        </div>
                        <h5 class="fw-bold text-dark mb-1">{{ $saver->student_name }}</h5>
                        <p class="text-muted small mb-2">No. Rekening: <strong class="text-primary font-monospace">{{ $saver->account_number }}</strong> | NIS: {{ $saver->student_id }}</p>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                            Kelas {{ $saver->grade_class ?? 'Siswa Aktif' }}
                        </span>
                    </div>
                    <div class="p-3 rounded-3" style="background: #F8FAFC; border: 1px dashed #CBD5E1;">
                        <small class="text-muted d-block text-center" style="font-size: 0.78rem; line-height: 1.45;">
                            <i class="bi bi-info-circle text-primary me-1"></i>
                            Tunjukkan layar QR ini ke alat scanner kasir Teller saat setor atau tarik tunai tanpa perlu kartu fisik.
                        </small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-4 px-4">
                    <button type="button" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold" data-bs-dismiss="modal">
                        Selesai
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL UBAH TARGET MENABUNG ===== -->
    <div class="modal fade" id="modalGoal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
                <form action="{{ route('saver.goal.update') }}" method="POST">
                    @csrf
                    <div class="modal-header border-0 pb-0 pt-4 px-4">
                        <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                            <i class="bi bi-piggy-bank-fill text-warning fs-5"></i> Atur Target Menabung (Celengan Impian)
                        </h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">Nama Impian / Rencana Tabungan</label>
                            <input type="text" name="goal_title" class="form-control rounded-3" placeholder="Contoh: Beli Laptop / Perlengkapan PKL" value="{{ $savingsGoal['title'] ?? '' }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold small text-dark">Nominal Target Tabungan (Rp)</label>
                            <input type="number" name="goal_amount" class="form-control rounded-3" placeholder="500000" min="10000" step="5000" value="{{ $savingsGoal['target'] ?? 500000 }}" required>
                            <small class="text-muted" style="font-size: 0.76rem;">Masukkan target minimal Rp 10.000.</small>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 pb-4 px-4">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">Simpan Target</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== MODAL KEAMANAN (GANTI PIN & PASSWORD) ===== -->
    <div class="modal fade" id="modalSecurity" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius: 24px; border: none; box-shadow: 0 25px 50px rgba(0,0,0,0.25);">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-shield-lock-fill text-primary fs-5"></i> Keamanan Akun Nasabah
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <!-- Nav Tabs Keamanan -->
                    <ul class="nav nav-pills nav-fill mb-3 p-1 rounded-pill bg-light" id="securityTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill py-2 small fw-semibold" id="tabPinBtn" data-bs-toggle="pill" data-bs-target="#tabPin" type="button">
                                <i class="bi bi-key me-1"></i> Ganti PIN 6-Digit
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill py-2 small fw-semibold" id="tabPassBtn" data-bs-toggle="pill" data-bs-target="#tabPass" type="button">
                                <i class="bi bi-lock me-1"></i> Ganti Kata Sandi
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="securityTabsContent">
                        <!-- Tab 1: Ganti PIN 6-Digit -->
                        <div class="tab-pane fade show active" id="tabPin" role="tabpanel">
                            <form action="{{ route('saver.security.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_type" value="pin">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">PIN Keamanan Lama</label>
                                    <input type="password" name="current_pin" maxlength="6" inputmode="numeric" class="form-control rounded-3 font-monospace text-center fs-5" placeholder="••••••" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">PIN Baru (6 Angka)</label>
                                    <input type="password" name="new_pin" maxlength="6" inputmode="numeric" class="form-control rounded-3 font-monospace text-center fs-5" placeholder="••••••" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Konfirmasi PIN Baru</label>
                                    <input type="password" name="new_pin_confirmation" maxlength="6" inputmode="numeric" class="form-control rounded-3 font-monospace text-center fs-5" placeholder="••••••" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                    Simpan PIN Baru
                                </button>
                            </form>
                        </div>

                        <!-- Tab 2: Ganti Kata Sandi -->
                        <div class="tab-pane fade" id="tabPass" role="tabpanel">
                            <form action="{{ route('saver.security.update') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action_type" value="password">
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Kata Sandi Saat Ini</label>
                                    <input type="password" name="current_password" class="form-control rounded-3" placeholder="Masukkan password saat ini" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Kata Sandi Baru</label>
                                    <input type="password" name="new_password" minlength="6" class="form-control rounded-3" placeholder="Minimal 6 karakter" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-dark">Ulangi Kata Sandi Baru</label>
                                    <input type="password" name="new_password_confirmation" minlength="6" class="form-control rounded-3" placeholder="Ulangi kata sandi baru" required>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill py-2 fw-semibold">
                                    Simpan Kata Sandi
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Client-side Scripts -->
    <script>
        // ===== 1. Sensor Saldo (Hide/Show Nominal) =====
        const originalBalanceText = "Rp {{ number_format($saver->saldo, 0, ',', '.') }}";
        const maskedBalanceText = "Rp ••••••••";
        const balanceDisplay = document.getElementById('balanceDisplay');
        const btnToggleBalance = document.getElementById('btnToggleBalance');
        const eyeIcon = document.getElementById('eyeIcon');
        const eyeText = document.getElementById('eyeText');

        let isBalanceHidden = localStorage.getItem('saver_balance_hidden') === 'true';

        function updateBalanceVisibility() {
            if (isBalanceHidden) {
                balanceDisplay.textContent = maskedBalanceText;
                eyeIcon.className = 'bi bi-eye';
                eyeText.textContent = 'Tampilkan';
            } else {
                balanceDisplay.textContent = originalBalanceText;
                eyeIcon.className = 'bi bi-eye-slash';
                eyeText.textContent = 'Sembunyikan';
            }
        }

        btnToggleBalance?.addEventListener('click', function() {
            isBalanceHidden = !isBalanceHidden;
            localStorage.setItem('saver_balance_hidden', isBalanceHidden);
            updateBalanceVisibility();
        });

        // Initialize visibility
        updateBalanceVisibility();

        // ===== 2. Copy No. Rekening =====
        function copyAccountNumber(accNumber) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(accNumber).then(() => {
                    alert('Nomor rekening ' + accNumber + ' berhasil disalin ke clipboard!');
                }).catch(() => {
                    prompt('Salin nomor rekening:', accNumber);
                });
            } else {
                prompt('Salin nomor rekening:', accNumber);
            }
        }

        // ===== 3. Filter Riwayat Transaksi (Semua / Setoran / Penarikan) =====
        function filterTransactions(type) {
            const cards = document.querySelectorAll('.trx-item-card');
            const btns = document.querySelectorAll('.filter-btn');

            btns.forEach(b => b.classList.remove('active'));
            event.target.classList.add('active');

            cards.forEach(card => {
                if (type === 'all' || card.getAttribute('data-type') === type) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // ===== 4. Dismiss PWA iOS Banner =====
        const pwaBanner = document.getElementById('pwaIosBanner');
        const btnClosePwa = document.getElementById('btnClosePwa');

        if (localStorage.getItem('hide_pwa_ios_banner') === 'true') {
            pwaBanner?.classList.add('hidden');
        }

        btnClosePwa?.addEventListener('click', function() {
            pwaBanner?.classList.add('hidden');
            localStorage.setItem('hide_pwa_ios_banner', 'true');
        });
    </script>
</body>
</html>
