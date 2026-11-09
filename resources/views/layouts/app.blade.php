<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Bank Mini SMKN 1 Bantul')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-smkn1bantul.png') }}">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <!-- Google Fonts: Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-main: #F8FAFC;
            --primary: #16233F;
            --primary-hover: #0E1830;
            --primary-navy: #16233F;
            --primary-light: #EFF6FF;
            --primary-border: #DBEAFE;
            --navy-deep: #0A1426;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-soft: #EEF2F6;
            --card-bg: #FFFFFF;
            --shadow-soft: 0 4px 20px -2px rgba(22, 35, 63, 0.05);
            --shadow-card: 0 2px 10px rgba(15, 23, 42, 0.03);
            --shadow-hover: 0 8px 24px rgba(22, 35, 63, 0.12);
            --radius-lg: 20px;
            --radius-md: 14px;
            --radius-sm: 10px;
            --sidebar-w: 256px;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-dark);
            font-size: 0.94rem;
            -webkit-font-smoothing: antialiased;
            overflow-x: hidden;
        }

        a { text-decoration: none; }

        /* ===== Scrollbar halus ===== */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: rgba(22, 35, 63, 0.18); border-radius: 10px; }
        ::-webkit-scrollbar-track { background: transparent; }

        /* ===== Layout Shell ===== */
        .app-shell {
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-main);
        }

        /* ===== Sidebar (Biru Dongker Curved Scoop Design) ===== */
        .sidebar {
            width: var(--sidebar-w);
            min-width: var(--sidebar-w);
            max-width: var(--sidebar-w);
            flex: 0 0 var(--sidebar-w);
            background: linear-gradient(180deg, #16233F 0%, #0E1830 100%);
            color: #FFFFFF;
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            height: 100vh;
            height: 100dvh;
            display: flex;
            flex-direction: column;
            padding: 20px 0 16px 18px;
            z-index: 1030;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: none;
            overflow: visible;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px 16px 6px;
            text-decoration: none;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-right: 18px;
            margin-bottom: 6px;
        }

        .sidebar-brand .brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            flex-shrink: 0;
            padding: 5px;
        }

        .sidebar-brand .brand-icon img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-brand .brand-text {
            line-height: 1.15;
            text-decoration: none;
        }

        .sidebar-brand .brand-text strong {
            display: block;
            font-weight: 700;
            font-size: 1.15rem;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .sidebar-brand .brand-text span {
            font-size: 0.72rem;
            color: rgba(255, 255, 255, 0.75);
            letter-spacing: 0.3px;
        }

        .sidebar-section-label {
            font-size: 0.68rem;
            color: rgba(255, 255, 255, 0.48);
            padding: 10px 14px 4px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            position: relative;
            z-index: 2;
            flex-shrink: 0;
        }

        .sidebar-nav {
            flex: 1 1 auto;
            overflow-y: auto;
            overflow-x: visible;
            display: flex;
            flex-direction: column;
            gap: 4px;
            position: relative;
            padding: 4px 0 12px 0;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 0px;
            display: none;
        }

        /* ===== Sliding Curved Scoop Glider ===== */
        .sidebar-glider {
            position: absolute;
            left: 0;
            right: -6px;
            height: 48px;
            min-height: 48px;
            flex-shrink: 0;
            background: var(--bg-main);
            border-top-left-radius: 30px;
            border-bottom-left-radius: 30px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            z-index: 1;
            pointer-events: none;
            opacity: 1;
            box-shadow: 6px 0 0 0 var(--bg-main);
            transition: transform 0.22s cubic-bezier(0.2, 0.9, 0.3, 1),
                        height 0.22s cubic-bezier(0.2, 0.9, 0.3, 1);
            will-change: transform;
            contain: layout paint;
        }

        .sidebar-glider::before {
            content: '';
            position: absolute;
            right: 0;
            top: -20px;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle at 0 0, transparent 19.5px, var(--bg-main) 20px);
            pointer-events: none;
        }

        .sidebar-glider::after {
            content: '';
            position: absolute;
            right: 0;
            bottom: -20px;
            width: 20px;
            height: 20px;
            background: radial-gradient(circle at 0 100%, transparent 19.5px, var(--bg-main) 20px);
            pointer-events: none;
        }

        .sidebar-nav .nav-link {
            position: relative;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
            color: rgba(255, 255, 255, 0.80);
            height: 48px;
            min-height: 48px;
            flex-shrink: 0;
            padding: 0 18px 0 18px;
            margin-right: -6px;
            border-top-left-radius: 30px;
            border-bottom-left-radius: 30px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            font-size: 0.88rem;
            font-weight: 500;
            line-height: 1;
            transition: color 0.22s ease, transform 0.18s ease, background-color 0.18s ease;
            background: transparent !important;
            text-decoration: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-nav .nav-link i {
            font-size: 1.12rem;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
            opacity: 0.85;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 2px;
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), color 0.25s ease, opacity 0.25s ease;
        }

        .sidebar-nav .nav-link:hover:not(.active) {
            background: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff;
            transform: translateX(4px);
        }

        .sidebar-nav .nav-link:hover:not(.active) i {
            opacity: 1;
            color: #38BDF8;
        }

        .sidebar-nav .nav-link.active {
            background: transparent !important;
            color: var(--primary) !important;
            font-weight: 600;
            box-shadow: none;
        }

        .sidebar-nav .nav-link.active i {
            color: var(--primary) !important;
            opacity: 1;
            transform: scale(1.08);
        }

        .sidebar-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 12px 18px 0 0;
            margin-top: auto;
        }

        .sidebar-footer .nav-link {
            color: rgba(255, 255, 255, 0.75);
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s ease;
            background: transparent;
        }
        
        .sidebar-footer .nav-link:hover {
            background: rgba(239, 68, 68, 0.15);
            color: #FCA5A5;
        }

        .sidebar-footer .nav-link:hover i {
            color: #F87171;
        }

        /* ===== Main content wrapper ===== */
        .main-wrapper {
            margin-left: var(--sidebar-w);
            flex: 1 1 auto;
            min-width: 0;
            width: calc(100% - var(--sidebar-w));
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: var(--bg-main);
        }

        /* ===== Topbar ===== */
        .topbar {
            position: sticky;
            top: 0;
            z-index: 1020;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 36px 18px 36px;
            background-color: var(--bg-main);
        }

        .topbar-page-title {
            font-size: 1.45rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        .topbar-page-title small {
            display: block;
            font-size: 0.8rem;
            font-weight: 400;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-pill-badge {
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            border-radius: 50px;
            padding: 6px 16px;
            font-size: 0.82rem;
            color: var(--text-muted);
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
        }

        .topbar-icon-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            font-size: 1.05rem;
            position: relative;
            transition: all 0.18s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            cursor: pointer;
        }

        .topbar-icon-btn:hover,
        .topbar-icon-btn[aria-expanded="true"] {
            border-color: var(--primary);
            color: var(--primary);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(12, 96, 240, 0.12);
        }

        .topbar-notif-dropdown {
            position: relative;
        }

        .topbar-icon-btn .notification-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 19px;
            height: 19px;
            padding: 0 4px;
            background: #EF4444;
            color: #FFFFFF;
            font-size: 0.68rem;
            font-weight: 700;
            border-radius: 50px;
            border: 2px solid #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.45);
            animation: notifPulse 2.2s infinite ease-in-out;
        }

        @keyframes notifPulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.12); }
            100% { transform: scale(1); }
        }

        .notif-dropdown-menu {
            width: 390px;
            max-width: calc(100vw - 32px);
            border-radius: 18px;
            border: 1px solid rgba(22, 35, 63, 0.1);
            box-shadow: 0 20px 45px rgba(22, 35, 63, 0.16), 0 4px 12px rgba(0, 0, 0, 0.04);
            margin-top: 10px !important;
            overflow: hidden;
            background: #FFFFFF;
            z-index: 1060;
        }

        .notif-header {
            padding: 16px 20px;
            background: #FFFFFF;
            border-bottom: 1px solid var(--border-soft);
        }

        .notif-mark-read-btn {
            font-size: 0.78rem;
            cursor: pointer;
            transition: opacity 0.15s ease;
        }

        .notif-mark-read-btn:hover {
            opacity: 0.8;
            text-decoration: underline !important;
        }

        .notif-body-list {
            max-height: 380px;
            overflow-y: auto;
            overscroll-behavior: contain;
        }

        .notif-body-list::-webkit-scrollbar {
            width: 5px;
        }
        .notif-body-list::-webkit-scrollbar-track {
            background: #F8FAFC;
        }
        .notif-body-list::-webkit-scrollbar-thumb {
            background: #CBD5E1;
            border-radius: 10px;
        }

        .notif-item {
            padding: 14px 18px;
            border-bottom: 1px solid #F1F5F9;
            background: #FFFFFF;
            transition: all 0.15s ease;
            cursor: pointer;
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item.unread {
            background: rgba(239, 246, 255, 0.65);
            border-left: 3.5px solid #2563EB;
        }

        .notif-item:hover {
            background: #F8FAFC;
            transform: translateX(2px);
        }

        .notif-icon-box {
            width: 38px;
            height: 38px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .notif-item-title {
            font-size: 0.88rem;
            line-height: 1.3;
        }

        .notif-item-badge {
            font-size: 0.68rem;
            padding: 3px 8px;
            font-weight: 600;
        }

        .notif-item-desc {
            font-size: 0.81rem;
            color: #475569;
            line-height: 1.42;
        }

        .notif-item-time {
            font-size: 0.74rem;
        }

        .notif-action-pill {
            font-size: 0.76rem;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .notif-footer {
            padding: 10px 16px;
            background: #F8FAFC;
            border-top: 1px solid var(--border-soft);
        }

        .notif-footer-link {
            font-size: 0.82rem;
            padding: 6px 12px;
            border-radius: 10px;
            transition: background-color 0.15s ease;
            text-decoration: none;
        }

        .notif-footer-link:hover {
            background: #EEF2F6;
            color: #16233F;
        }

        .topbar-user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 4px 14px 4px 5px;
            border-radius: 50px;
            background: #FFFFFF;
            border: 1px solid var(--border-soft);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
        }

        .topbar-user-info:hover {
            border-color: #3B5BA5;
            box-shadow: 0 4px 12px rgba(22, 35, 63, 0.12);
            transform: translateY(-1px);
        }

        .topbar-user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #16233F 0%, #2A3E63 100%);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.85rem;
            box-shadow: 0 2px 6px rgba(22, 35, 63, 0.25);
        }

        .topbar-user-text strong {
            display: block;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-dark);
            line-height: 1.2;
        }

        .topbar-user-text span {
            font-size: 0.72rem;
            color: var(--text-muted);
            line-height: 1.1;
        }

        /* ===== Top Progress Bar (Page Transition Indicator) ===== */
        #topProgressBar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0%;
            background: linear-gradient(90deg, #16233F 0%, #3B82F6 100%);
            box-shadow: 0 0 12px rgba(22, 35, 63, 0.5);
            z-index: 99999;
            transition: width 0.35s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
            opacity: 0;
            pointer-events: none;
        }

        /* ===== Content area ===== */
        .content-area {
            padding: 8px 36px 40px 36px;
            flex: 1;
            animation: pageFadeIn 0.32s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        @keyframes pageFadeIn {
            0% {
                opacity: 0;
                transform: translateY(12px);
            }
            100% {
                opacity: 1;
                transform: none;
            }
        }

        /* Modal Z-Index and Backdrop Fix */
        .modal {
            z-index: 1060 !important;
        }
        .modal-backdrop {
            z-index: 1050 !important;
        }
        .modal-dialog {
            z-index: 1065 !important;
        }

        /* ===== Modern Floating Card & Tables (Matches Reference) ===== */
        .card, .card-glass, .glass-card, .card-solid {
            background: #FFFFFF !important;
            border: 1px solid var(--border-soft) !important;
            border-radius: var(--radius-lg) !important;
            box-shadow: var(--shadow-card) !important;
            padding: 1.75rem;
            margin-bottom: 1.75rem;
            transition: all 0.2s ease;
        }

        /* Clean Table Header & Floating Row Items */
        .table-responsive {
            overflow-x: auto;
            border: none !important;
        }

        .table {
            width: 100%;
            border-collapse: separate !important;
            border-spacing: 0 10px !important;
            margin-top: -6px;
            background: transparent !important;
        }

        .table thead th {
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 18px;
            border: none !important;
            background: transparent !important;
        }

        .table tbody tr {
            background: #FFFFFF !important;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            border-radius: 14px;
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .table tbody td {
            padding: 16px 18px;
            color: var(--text-dark);
            vertical-align: middle;
            border: none !important;
            border-top: 1px solid #EEF2F6 !important;
            border-bottom: 1px solid #EEF2F6 !important;
            background: #FFFFFF !important;
        }

        .table tbody tr td:first-child {
            border-top-left-radius: 14px;
            border-bottom-left-radius: 14px;
            border-left: 1px solid #EEF2F6 !important;
        }

        .table tbody tr td:last-child {
            border-top-right-radius: 14px;
            border-bottom-right-radius: 14px;
            border-right: 1px solid #EEF2F6 !important;
        }

        .table tbody tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(22, 35, 63, 0.08);
        }

        .table tbody tr:hover td {
            background: #FFFFFF !important;
            border-color: #D8E5FC !important;
        }

        /* Buttons */
        .btn {
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.88rem;
            padding: 9px 20px;
            transition: all 0.2s ease;
        }

        .btn-primary, .btn-primary-custom {
            background: var(--primary) !important;
            border-color: var(--primary) !important;
            color: #FFFFFF !important;
            box-shadow: 0 4px 14px rgba(22, 35, 63, 0.22);
        }

        .btn-primary:hover, .btn-primary-custom:hover {
            background: var(--primary-hover) !important;
            border-color: var(--primary-hover) !important;
            color: #FFFFFF !important;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(22, 35, 63, 0.3);
        }

        .btn-dark {
            background: #0F172A !important;
            border-color: #0F172A !important;
            color: #FFFFFF !important;
        }

        .btn-dark:hover {
            background: #1E293B !important;
            transform: translateY(-1px);
        }

        .btn-light, .btn-secondary {
            background: #FFFFFF !important;
            border: 1px solid var(--border-soft) !important;
            color: var(--text-dark) !important;
        }

        .btn-light:hover, .btn-secondary:hover {
            background: #F8FAFC !important;
            border-color: #CBD5E1 !important;
            color: var(--primary) !important;
        }

        /* Status Pills & Badges */
        .badge {
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 500;
            font-size: 0.78rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .badge-soft-success, .badge-success, .badge-approved, .badge-debit {
            background-color: #ECFDF5 !important;
            color: #059669 !important;
            border: 1px solid #A7F3D0 !important;
        }

        .badge-soft-warning, .badge-warning, .badge-pending {
            background-color: #FFFBEB !important;
            color: #D97706 !important;
            border: 1px solid #FDE68A !important;
        }

        .badge-soft-danger, .badge-danger, .badge-rejected, .badge-credit {
            background-color: #FEF2F2 !important;
            color: #DC2626 !important;
            border: 1px solid #FECACA !important;
        }

        .badge-soft-primary, .badge-primary {
            background-color: #EFF6FF !important;
            color: var(--primary) !important;
            border: 1px solid #BFDBFE !important;
        }

        /* Form Inputs */
        .form-control {
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            padding: 10px 14px;
            font-size: 0.88rem;
            color: var(--text-dark);
            background-color: #FFFFFF;
            transition: all 0.2s ease;
        }
        .form-select {
            border-radius: 12px;
            border: 1px solid #E2E8F0;
            padding: 10px 2.25rem 10px 14px;
            font-size: 0.88rem;
            color: var(--text-dark);
            background-color: #FFFFFF;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(12, 96, 240, 0.12);
            outline: none;
        }

        /* ===== Global Modern Pagination Buttons ===== */
        .pagination {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            justify-content: flex-end !important;
            gap: 6px !important;
            margin: 0 !important;
            list-style: none !important;
            padding: 0 !important;
        }
        .pagination .page-item {
            margin: 0 !important;
        }
        .pagination .page-item .page-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            min-width: 36px !important;
            height: 36px !important;
            padding: 6px 12px !important;
            font-size: 0.85rem !important;
            font-weight: 500 !important;
            font-family: 'Poppins', sans-serif !important;
            color: #334155 !important;
            background-color: #FFFFFF !important;
            border: 1px solid #E2E8F0 !important;
            border-radius: 10px !important;
            text-decoration: none !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04) !important;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
            cursor: pointer !important;
        }
        .pagination .page-item .page-link:hover:not(.active) {
            background-color: #F8FAFC !important;
            color: #1E3A8A !important;
            border-color: #CBD5E1 !important;
            transform: translateY(-1px) !important;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, #16233F 0%, #1E3A8A 100%) !important;
            color: #FFFFFF !important;
            border-color: #16233F !important;
            font-weight: 600 !important;
            box-shadow: 0 4px 12px rgba(22, 35, 63, 0.2) !important;
        }
        .pagination .page-item.disabled .page-link {
            color: #94A3B8 !important;
            background-color: #F8FAFC !important;
            border-color: #E2E8F0 !important;
            cursor: not-allowed !important;
            opacity: 0.6 !important;
            box-shadow: none !important;
        }
        /* Defeat any SVG arrows blowing up to screen width */
        .pagination svg,
        nav[role="navigation"] svg {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            display: inline-block !important;
        }

        /* Mobile Adjustments */
        .sidebar-toggle-btn {
            display: none;
        }

        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px;
                min-width: 280px;
                max-width: 85vw;
                box-shadow: 0 16px 40px rgba(12, 96, 240, 0.18);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-wrapper {
                margin-left: 0;
                width: 100%;
                min-width: 0;
            }
            .sidebar-toggle-btn {
                display: flex;
            }
            .sidebar-backdrop {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(15, 23, 42, 0.4);
                z-index: 1025;
            }
            .sidebar-backdrop.show {
                display: block;
            }
            .topbar {
                padding: 14px 16px;
            }
            .topbar-title {
                font-size: 1.15rem !important;
                max-width: 240px;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .content-area {
                padding: 16px 14px 32px 14px;
            }
            .table-responsive {
                -webkit-overflow-scrolling: touch;
                border-radius: 14px;
            }
        }

        @media (max-width: 575.98px) {
            .topbar {
                padding: 12px 14px !important;
            }
            .topbar-title {
                font-size: 1.05rem !important;
                max-width: 180px;
            }
            .topbar-subtitle {
                display: none !important;
            }
            .topbar-user-text { display: none !important; }
            .topbar-pill-badge { display: none !important; }
            .topbar-user-btn {
                padding: 4px 6px !important;
            }
            .pagination {
                justify-content: center !important;
            }
            .pagination .page-item .page-link {
                min-width: 32px !important;
                height: 32px !important;
                padding: 4px 8px !important;
                font-size: 0.78rem !important;
            }
        }

        /* ===== Receipt Print Mode (Hanya cetak struk tanpa tumpuk background) ===== */
        @media print {
            /* Sembunyikan semua elemen di body */
            body * {
                visibility: hidden !important;
            }

            /* Khusus area struk & seluruh elemen di dalamnya dibuat visible */
            #printableReceipt,
            #printableReceipt *:not(.no-print):not(.no-print *),
            #printableFocusReceipt,
            #printableFocusReceipt *:not(.no-print):not(.no-print *) {
                visibility: visible !important;
            }

            /* Pastikan tombol aksi cetak / tutup di dalam struk benar-benar hilang */
            .no-print,
            .no-print *,
            #printableReceipt .no-print,
            #printableReceipt .no-print *,
            #printableFocusReceipt .no-print,
            #printableFocusReceipt .no-print * {
                display: none !important;
                visibility: hidden !important;
                height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                border: 0 !important;
            }

            html, body {
                background: #FFFFFF !important;
                background-color: #FFFFFF !important;
                color: #0F172A !important;
                margin: 0 !important;
                padding: 0 !important;
                height: auto !important;
                min-height: 0 !important;
                overflow: visible !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .modal,
            .modal-backdrop,
            #modalReceipt,
            #modalFocusReceipt {
                background: transparent !important;
                background-color: transparent !important;
                box-shadow: none !important;
            }

            #printableReceipt,
            #printableFocusReceipt {
                position: absolute !important;
                left: 50% !important;
                top: 0 !important;
                transform: translateX(-50%) !important;
                display: block !important;
                padding: 16px 20px !important;
                margin: 0 auto !important;
                width: 330px !important;
                max-width: 95% !important;
                color: #0F172A !important;
                background: #FFFFFF !important;
                border: 1px dashed #64748B !important;
                border-radius: 12px !important;
                box-shadow: none !important;
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            #printableReceipt .bg-light,
            #printableFocusReceipt .bg-light {
                background-color: #F8FAFC !important;
                border: 1px dashed #CBD5E1 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            @page {
                size: auto;
                margin: 4mm;
            }
        }
    </style>

    @stack('styles')
</head>
<body>

    <div id="topProgressBar" class="no-print"></div>

    <div class="app-shell">

        <!-- ===== Sidebar ===== -->
        <aside class="sidebar no-print" id="appSidebar">
            <a href="/" class="sidebar-brand">
                <div class="brand-icon">
                    <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul">
                </div>
                <div class="brand-text">
                    <strong>Bank Mini</strong>
                    <span>SMKN 1 Bantul</span>
                </div>
            </a>

            <nav class="sidebar-nav" id="sidebarNav">
                <div class="sidebar-glider" id="sidebarGlider"></div>
                @auth
                    @if(in_array(Auth::user()->role, ['Administrator', 'Admin', 'Admin Utama', 'admin_utama', 'Supervisor']))
                        <span class="sidebar-section-label">Menu Utama</span>
                        <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                        <a href="{{ route('admin.savers.index') }}" class="nav-link {{ request()->routeIs('admin.savers.*') || request()->routeIs('admin.customers.*') ? 'active' : '' }}">
                            <i class="bi bi-people-fill"></i> Data Nasabah
                        </a>
                        <a href="{{ route('admin.members.index') }}" class="nav-link {{ request()->routeIs('admin.members.*') || request()->routeIs('admin.member-data.*') ? 'active' : '' }}">
                            <i class="bi bi-person-lines-fill"></i> Data Anggota
                        </a>
                        @if(Route::has('admin.alumni.index'))
                        <a href="{{ route('admin.alumni.index') }}" class="nav-link {{ request()->routeIs('admin.alumni.*') ? 'active' : '' }}">
                            <i class="bi bi-mortarboard-fill"></i> Data Siswa Lulus
                        </a>
                        @endif
                        @if(Route::has('admin.transactions.index'))
                        <a href="{{ route('admin.transactions.index') }}" class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-left-right"></i> Riwayat Transaksi
                        </a>
                        @endif

                        <span class="sidebar-section-label">Laporan & Audit</span>
                        <a href="{{ route('admin.withdrawal-requests.index') }}" class="nav-link {{ request()->routeIs('admin.withdrawal-requests.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check-fill"></i> Pengajuan Penarikan
                        </a>
                        <a href="{{ route('admin.approval.index') }}" class="nav-link {{ request()->routeIs('admin.approval.*') || request()->routeIs('admin.shifts.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-check"></i> Approval Kas Teller
                        </a>
                        <a href="{{ route('admin.reports.monthly') }}" class="nav-link {{ request()->routeIs('admin.reports.monthly*') ? 'active' : '' }}">
                            <i class="bi bi-calendar3-range"></i> Laporan Bulanan
                        </a>
                        <a href="{{ route('admin.journals.index') }}" class="nav-link {{ request()->routeIs('admin.journals.*') || request()->routeIs('admin.balance.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-bookmark-fill"></i> Jurnal Akuntansi
                        </a>
                        <a href="{{ route('admin.audit.index') }}" class="nav-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock-fill"></i> Audit Log Sistem
                        </a>

                        <span class="sidebar-section-label">Sistem</span>
                        <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge-fill"></i> Manajemen Pegawai
                        </a>
                        <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                            <i class="bi bi-gear-fill"></i> Pengaturan Sistem
                        </a>
                    @elseif(Auth::user()->role === 'Teller')
                        <span class="sidebar-section-label">Menu Utama</span>
                        <a href="{{ route('teller.dashboard') }}" class="nav-link {{ request()->routeIs('teller.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-grid-1x2-fill"></i> Dashboard
                        </a>
                        <a href="{{ route('teller.activations.index') }}" class="nav-link {{ request()->routeIs('teller.activations.*') ? 'active' : '' }}">
                            <i class="bi bi-person-check-fill"></i> Aktivasi Nasabah
                        </a>
                        <a href="{{ route('teller.setoran.create') }}" class="nav-link {{ request()->routeIs('teller.setoran.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-down-circle-fill"></i> Setoran Tunai
                        </a>
                        <a href="{{ route('teller.penarikan.create') }}" class="nav-link {{ request()->routeIs('teller.penarikan.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-up-circle-fill"></i> Penarikan Tunai
                        </a>
                        <a href="{{ route('teller.withdrawal-requests.index') }}" class="nav-link {{ request()->routeIs('teller.withdrawal-requests.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check-fill"></i> Pengajuan Penarikan
                        </a>

                        <span class="sidebar-section-label">Laporan</span>
                        <a href="{{ route('teller.report.closing') }}" class="nav-link {{ request()->routeIs('teller.report.*') ? 'active' : '' }}">
                            <i class="bi bi-journal-check"></i> Penutupan Kas
                        </a>
                    @endif
                @endauth
                <script>
                    (function() {
                        try {
                            var nav = document.getElementById('sidebarNav');
                            var g = document.getElementById('sidebarGlider');
                            if (nav && g) {
                                var a = nav.querySelector('.nav-link.active');
                                if (a) {
                                    g.style.transition = 'none';
                                    g.style.transform = 'translateY(' + a.offsetTop + 'px)';
                                    g.style.height = '48px';
                                    g.style.opacity = '1';
                                }
                            }
                        } catch(e) {}
                    })();
                </script>
            </nav>

            <div class="sidebar-footer">
                @auth
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-left"></i> Keluar
                    </a>
                @endauth
            </div>
        </aside>

        <div class="sidebar-backdrop no-print" id="sidebarBackdrop"></div>

        <!-- ===== Main Wrapper ===== -->
        <div class="main-wrapper">

            <!-- ===== Topbar ===== -->
            <header class="topbar no-print">
                <div class="d-flex align-items-center gap-3">
                    <button class="topbar-icon-btn sidebar-toggle-btn" id="sidebarToggle" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="topbar-page-title">
                        @if(View::hasSection('header_title'))
                            @yield('header_title')
                        @elseif(request()->routeIs('admin.alumni.*'))
                            Data Siswa Lulus (Alumni)
                        @elseif(request()->routeIs('admin.transactions.*'))
                            Riwayat Transaksi
                        @elseif(request()->routeIs('admin.balance.*'))
                            Ringkasan Saldo Tersimpan
                        @elseif(request()->routeIs('admin.approval.*') || request()->routeIs('admin.shifts.*'))
                            Approval Kas Teller
                        @elseif(request()->routeIs('admin.reports.*'))
                            Laporan Bulanan
                        @elseif(request()->routeIs('admin.journals.*'))
                            Jurnal Akuntansi
                        @elseif(request()->routeIs('admin.audit.*'))
                            Audit Log Sistem
                        @elseif(request()->routeIs('admin.savers.*') || request()->routeIs('admin.customers.*'))
                            Data Nasabah
                        @elseif(request()->routeIs('admin.members.*') || request()->routeIs('admin.member-data.*'))
                            Data Anggota
                        @elseif(request()->routeIs('admin.users.*') || request()->routeIs('admin.employees.*'))
                            Manajemen Pegawai
                        @elseif(request()->routeIs('admin.settings.*') || request()->routeIs('settings.*'))
                            Pengaturan Sistem
                        @elseif(request()->routeIs('teller.setoran.*') || request()->routeIs('teller.transaction.deposit') || request()->is('teller/setoran*') || request()->is('teller/deposit*'))
                            Setoran Tunai
                        @elseif(request()->routeIs('teller.penarikan.*') || request()->routeIs('teller.transaction.withdrawal') || request()->is('teller/penarikan*') || request()->is('teller/withdrawal*'))
                            Penarikan Tunai
                        @elseif(request()->routeIs('teller.activations.*'))
                            Aktivasi Nasabah
                        @elseif(request()->routeIs('teller.report.*'))
                            Penutupan Kas
                        @elseif(request()->routeIs('teller.shift.create'))
                            Buka Laci Kas
                        @elseif(request()->routeIs('teller.shift.close'))
                            Tutup Laci Kas
                        @elseif(request()->routeIs('teller.dashboard') || request()->routeIs('teller.transactions.*'))
                            Dashboard
                        @elseif(View::hasSection('page-title'))
                            @yield('page-title')
                        @elseif(View::hasSection('title'))
                            @yield('title')
                        @else
                            Dashboard
                        @endif
                    </div>
                </div>

                <div class="topbar-actions">
                    <div class="topbar-pill-badge d-none d-md-flex">
                        <i class="bi bi-calendar-check text-primary"></i>
                        <span>{{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</span>
                    </div>

                    <!-- Notifikasi Sistem Dropdown -->
                    <div class="dropdown topbar-notif-dropdown">
                        <button class="topbar-icon-btn d-none d-sm-flex" type="button" id="notifDropdownToggle" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Notifikasi Sistem">
                            <i class="bi bi-bell"></i>
                            @if(($unreadNotificationCount ?? 0) > 0)
                                <span class="notification-badge" id="notifBadgeCount">{{ ($unreadNotificationCount ?? 0) > 9 ? '9+' : $unreadNotificationCount }}</span>
                            @endif
                        </button>

                        <div class="dropdown-menu dropdown-menu-end notif-dropdown-menu p-0 shadow-lg" aria-labelledby="notifDropdownToggle">
                            <div class="notif-header d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                        <i class="bi bi-bell-fill text-primary"></i> Notifikasi Sistem
                                    </h6>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge {{ ($unreadNotificationCount ?? 0) > 0 ? 'bg-danger-subtle text-danger' : 'bg-secondary-subtle text-muted' }} rounded-pill" id="notifStatusBadge" style="font-size: 0.72rem; font-weight: 600;">
                                            {{ ($unreadNotificationCount ?? 0) > 0 ? $unreadNotificationCount . ' Perlu Perhatian' : 'Semua Terpantau' }}
                                        </span>
                                    </div>
                                </div>
                                @if(($unreadNotificationCount ?? 0) > 0)
                                    <button type="button" class="btn btn-sm btn-link text-decoration-none text-primary fw-medium p-0 notif-mark-read-btn" id="markAllReadBtn" title="Tandai semua telah dibaca">
                                        <i class="bi bi-check2-all me-1"></i>Tandai Dibaca
                                    </button>
                                @endif
                            </div>

                            <div class="notif-body-list" id="notifListContainer">
                                @forelse(($systemNotifications ?? []) as $notif)
                                    <a href="{{ $notif['url'] }}" class="notif-item {{ $notif['is_unread'] ? 'unread' : '' }} text-decoration-none d-flex align-items-start gap-3">
                                        <div class="notif-icon-box {{ $notif['icon_bg'] }}">
                                            <i class="{{ $notif['icon'] }}"></i>
                                        </div>
                                        <div class="notif-item-body flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                                <span class="notif-item-title fw-bold text-dark">{{ $notif['title'] }}</span>
                                                <span class="badge {{ $notif['badge_class'] }} rounded-pill notif-item-badge">{{ $notif['badge'] }}</span>
                                            </div>
                                            <p class="notif-item-desc mb-1">{{ $notif['message'] }}</p>
                                            <div class="d-flex align-items-center justify-content-between pt-1">
                                                <span class="notif-item-time text-muted small"><i class="bi bi-clock me-1"></i>{{ $notif['time'] }}</span>
                                                @if($notif['has_action'] ?? false)
                                                    <span class="notif-action-pill small fw-semibold text-primary">Proses Sekarang <i class="bi bi-arrow-right"></i></span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="notif-empty text-center py-5 px-4">
                                        <div class="notif-empty-icon mb-2">
                                            <i class="bi bi-bell-slash text-muted" style="font-size: 2.4rem; opacity: 0.45;"></i>
                                        </div>
                                        <p class="fw-semibold text-dark mb-1" style="font-size: 0.92rem;">Tidak Ada Notifikasi Baru</p>
                                        <small class="text-muted d-block" style="font-size: 0.8rem; line-height: 1.4;">
                                            Semua laporan kas harian dan aktivitas operasional sudah dalam status selesai atau telah diverifikasi.
                                        </small>
                                    </div>
                                @endforelse
                            </div>

                            <div class="notif-footer">
                                @if(Auth::check() && in_array(Auth::user()->role, ['Administrator', 'Supervisor', 'Admin', 'Admin Utama', 'admin_utama']))
                                    <a href="{{ route('admin.approval.index') }}" class="btn btn-sm w-100 fw-semibold text-primary notif-footer-link d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-shield-check"></i> Buka Menu Approval Kas Teller <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                @elseif(Auth::check() && Auth::user()->role === 'Teller')
                                    <a href="{{ route('teller.report.closing') }}" class="btn btn-sm w-100 fw-semibold text-primary notif-footer-link d-flex align-items-center justify-content-center gap-1">
                                        <i class="bi bi-journal-check"></i> Buka Penutupan Kas Harian <i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @auth
                        <a href="{{ route('profile.show') }}" class="topbar-user-info text-decoration-none" title="Buka Profil Saya & Pengaturan Akun" style="cursor: pointer;">
                            <div class="topbar-user-avatar">
                                {{ strtoupper(substr(Auth::user()->officer_name ?? Auth::user()->username ?? 'A', 0, 1)) }}
                            </div>
                            <div class="topbar-user-text">
                                <strong>{{ Auth::user()->officer_name ?? Auth::user()->username ?? 'User' }}</strong>
                                <span>{{ Auth::user()->role ?? 'Pegawai' }}</span>
                            </div>
                            <i class="bi bi-chevron-right text-muted ms-1" style="font-size: 0.72rem;"></i>
                        </a>
                    @else
                        <div class="topbar-user-info">
                            <div class="topbar-user-avatar">G</div>
                            <div class="topbar-user-text">
                                <strong>Guest</strong>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            <!-- ===== Floating Action Toast Notification (Global) ===== -->
            @if(session('success') || session('error') || $errors->any())
                <div class="position-fixed top-0 end-0 p-3 no-print" style="z-index: 9999; max-width: 420px; width: 100%; pointer-events: none;">
                    @if(session('success'))
                        <div class="toast show align-items-center border-0 shadow-lg mb-2 w-100" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 16px; background: #ECFDF5; color: #065F46; border-left: 5px solid #10B981 !important; pointer-events: auto;">
                            <div class="d-flex p-3 align-items-center">
                                <i class="bi bi-check-circle-fill fs-4 me-3 text-success"></i>
                                <div class="toast-body p-0 fw-semibold" style="font-size: 0.85rem;">
                                    <div class="fw-bold text-success mb-1">Aksi Berhasil</div>
                                    {{ session('success') }}
                                </div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif

                    @if(session('error') || $errors->any())
                        <div class="toast show align-items-center border-0 shadow-lg w-100" role="alert" aria-live="assertive" aria-atomic="true" style="border-radius: 16px; background: #FEF2F2; color: #991B1B; border-left: 5px solid #EF4444 !important; pointer-events: auto;">
                            <div class="d-flex p-3 align-items-center">
                                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                                <div class="toast-body p-0 fw-semibold" style="font-size: 0.85rem;">
                                    <div class="fw-bold text-danger mb-1">Aksi Gagal</div>
                                    {{ session('error') ?: $errors->first() }}
                                </div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- ===== Content ===== -->
            <main class="content-area">
                @if (session('success') && !request()->routeIs('profile.*') && !request()->routeIs('teller.activations.*'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 no-print" role="alert" style="border-radius: 12px; border: 1px solid #A7F3D0; background-color: #D1FAE5; color: #065F46;">
                        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error') && !request()->routeIs('profile.*') && !request()->routeIs('teller.activations.*'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm d-flex align-items-center gap-2 no-print" role="alert" style="border-radius: 12px; border: 1px solid #FECACA; background-color: #FEE2E2; color: #991B1B;">
                        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @yield('content')
            </main>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const sidebar = document.getElementById('appSidebar');
        const backdrop = document.getElementById('sidebarBackdrop');
        const toggleBtn = document.getElementById('sidebarToggle');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            backdrop.classList.toggle('show');
        }

        toggleBtn?.addEventListener('click', toggleSidebar);
        backdrop?.addEventListener('click', toggleSidebar);

        // Auto close sidebar on mobile when nav link is clicked
        if (sidebar) {
            sidebar.querySelectorAll('.nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.remove('show');
                        backdrop?.classList.remove('show');
                    }
                });
            });
        }

        // Auto-hide global toast notifications after 5.5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                document.querySelectorAll('.toast.show').forEach(function(t) {
                    t.classList.remove('show');
                    t.style.display = 'none';
                });
            }, 5500);
        });

        // Pindahkan modal langsung ke <body> saat dibuka agar tidak terperangkap dalam stacking context / transform
        document.addEventListener('show.bs.modal', function (event) {
            const modal = event.target;
            if (modal && modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }
        });

        // Auto-format Rupiah untuk input berkelas 'rupiah-input'
        document.addEventListener('DOMContentLoaded', function() {
            const rupiahInputs = document.querySelectorAll('.rupiah-input');
            
            rupiahInputs.forEach(function(input) {
                input.addEventListener('keyup', function(e) {
                    this.value = formatRupiah(this.value);
                });
                
                input.closest('form')?.addEventListener('submit', function() {
                    input.value = input.value.replace(/\./g, '');
                });
            });
        });

        function formatRupiah(angka, prefix){
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
            split       = number_string.split(','),
            sisa        = split[0].length % 3,
            rupiah      = split[0].substr(0, sisa),
            ribuan      = split[0].substr(sisa).match(/\d{3}/gi);
 
            if(ribuan){
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
 
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? 'Rp ' + rupiah : '');
        }

        // ===== Sliding Sidebar Glider & Smooth Page Transition =====
        const sidebarNav = document.getElementById('sidebarNav');
        const sidebarGlider = document.getElementById('sidebarGlider');
        const progressBar = document.getElementById('topProgressBar');

        function moveSidebarGlider(targetLink, animate = true) {
            if (!sidebarNav || !sidebarGlider || !targetLink) return;

            const topPos = targetLink.offsetTop;
            const itemHeight = 48; // Konsisten 48px baik Admin maupun Teller

            if (!animate) {
                sidebarGlider.style.transition = 'none';
                sidebarGlider.style.height = itemHeight + 'px';
                sidebarGlider.style.transform = `translateY(${topPos}px)`;
                sidebarGlider.style.opacity = '1';
                requestAnimationFrame(() => {
                    sidebarGlider.style.transition = 'transform 0.22s cubic-bezier(0.2, 0.9, 0.3, 1), height 0.22s cubic-bezier(0.2, 0.9, 0.3, 1)';
                });
            } else {
                sidebarGlider.style.transition = 'transform 0.22s cubic-bezier(0.2, 0.9, 0.3, 1), height 0.22s cubic-bezier(0.2, 0.9, 0.3, 1)';
                sidebarGlider.style.height = itemHeight + 'px';
                sidebarGlider.style.transform = `translateY(${topPos}px)`;
                sidebarGlider.style.opacity = '1';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (!sidebarNav || !sidebarGlider) return;

            const activeLink = sidebarNav.querySelector('.nav-link.active');
            if (activeLink) {
                const prevTop = sessionStorage.getItem('bankmini_active_glider_top');
                const targetTop = activeLink.offsetTop;

                if (prevTop !== null && Math.abs(Number(prevTop) - targetTop) > 4) {
                    // Luncurkan dari posisi menu sebelumnya secara mulus tanpa kedip
                    sidebarGlider.style.transition = 'none';
                    sidebarGlider.style.height = '48px';
                    sidebarGlider.style.transform = `translateY(${prevTop}px)`;
                    sidebarGlider.style.opacity = '1';

                    requestAnimationFrame(() => {
                        sidebarGlider.style.transition = 'transform 0.22s cubic-bezier(0.2, 0.9, 0.3, 1)';
                        sidebarGlider.style.transform = `translateY(${targetTop}px)`;
                    });
                } else {
                    moveSidebarGlider(activeLink, false);
                }

                sessionStorage.setItem('bankmini_active_glider_top', targetTop);

                // Auto-scroll tanpa animasi getar/kedip jika item berada di luar viewport sidebar
                const navRect = sidebarNav.getBoundingClientRect();
                const linkRect = activeLink.getBoundingClientRect();
                if (linkRect.bottom > navRect.bottom || linkRect.top < navRect.top) {
                    sidebarNav.scrollTop = Math.max(0, targetTop - 70);
                }
            }

            // Saat klik item di sidebar, jalankan luncuran mulus terlebih dahulu sebelum navigasi
            const navLinks = sidebarNav.querySelectorAll('.nav-link');
            navLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (this.classList.contains('active')) return;
                    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.button !== 0 || this.target === '_blank') return;

                    const href = this.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('javascript:')) return;

                    e.preventDefault();

                    navLinks.forEach(l => l.classList.remove('active'));
                    this.classList.add('active');

                    // Luncurkan putih glider secara mulus ke item tujuan
                    sessionStorage.setItem('bankmini_active_glider_top', this.offsetTop);
                    moveSidebarGlider(this, true);

                    if (progressBar) {
                        progressBar.style.opacity = '1';
                        progressBar.style.width = '70%';
                    }
                    const content = document.querySelector('.content-area');
                    if (content) {
                        content.style.transition = 'opacity 0.15s ease';
                        content.style.opacity = '0.55';
                    }

                    // Navigasi halaman tepat setelah luncuran mencapai posisi tujuan (180ms)
                    setTimeout(() => {
                        window.location.href = href;
                    }, 180);
                });
            });

            // Re-align saat window di-resize atau di-zoom
            window.addEventListener('resize', () => {
                const currentActive = sidebarNav.querySelector('.nav-link.active');
                if (currentActive) {
                    moveSidebarGlider(currentActive, false);
                }
            });
        });

        window.addEventListener('pageshow', function() {
            if (progressBar) {
                progressBar.style.width = '100%';
                setTimeout(() => {
                    progressBar.style.opacity = '0';
                    setTimeout(() => {
                        progressBar.style.width = '0%';
                    }, 250);
                }, 120);
            }
        });

        document.addEventListener('click', function(e) {
            const link = e.target.closest('a');
            if (!link) return;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript') || href === '' || 
                link.target === '_blank' || link.hasAttribute('download') || 
                link.getAttribute('data-bs-toggle') || link.classList.contains('dropdown-toggle')) {
                return;
            }

            if (link.hostname === window.location.hostname) {
                if (progressBar) {
                    progressBar.style.opacity = '1';
                    progressBar.style.width = '72%';
                }
                const content = document.querySelector('.content-area');
                if (content) {
                    content.style.transition = 'opacity 0.18s ease, transform 0.18s ease';
                    content.style.opacity = '0.4';
                    content.style.transform = 'translateY(-6px)';
                }
            }
        });

        // ===== Notification System AJAX & Idle Session Auto-Logout =====
        document.addEventListener('DOMContentLoaded', function() {
            const markAllReadBtn = document.getElementById('markAllReadBtn');
            if (markAllReadBtn) {
                markAllReadBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    fetch("{{ route('notifications.mark-read') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    }).then(res => res.json()).then(data => {
                        const badgeCount = document.getElementById('notifBadgeCount');
                        if (badgeCount) badgeCount.remove();
                        
                        const statusBadge = document.getElementById('notifStatusBadge');
                        if (statusBadge) {
                            statusBadge.className = 'badge bg-secondary-subtle text-muted rounded-pill';
                            statusBadge.textContent = 'Semua Terpantau';
                        }
                        
                        document.querySelectorAll('.notif-item.unread').forEach(item => {
                            item.classList.remove('unread');
                        });

                        markAllReadBtn.remove();
                    }).catch(err => console.error('Error marking notifications as read:', err));
                });
            }

            @auth
            // Sistem Idle Session Timeout (Otomatis kembali ke halaman Login jika tidak ada aksi selama 15 menit)
            const IDLE_LIMIT_MS = 15 * 60 * 1000; // 15 menit
            let lastUserActivity = Date.now();

            function resetIdleTimer() {
                lastUserActivity = Date.now();
            }

            ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart', 'click'].forEach(function(evt) {
                window.addEventListener(evt, resetIdleTimer, { passive: true });
            });

            function triggerAutoLogout() {
                fetch("{{ route('logout') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                }).finally(function() {
                    window.location.href = "{{ route('login', ['expired' => 1]) }}";
                });
            }

            // Cek aktivitas idle setiap 15 detik
            setInterval(function() {
                if (Date.now() - lastUserActivity >= IDLE_LIMIT_MS) {
                    triggerAutoLogout();
                }
            }, 15000);

            // Pengecekan notifikasi berkala di latar belakang (hanya jika pengguna baru saja aktif dalam 2 menit terakhir agar tidak menahan session yang idle)
            setInterval(function() {
                if (Date.now() - lastUserActivity > 2 * 60 * 1000) {
                    return;
                }

                fetch("{{ route('notifications.fetch') }}", {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => {
                    if (res.status === 401 || res.status === 419) {
                        window.location.href = "{{ route('login', ['expired' => 1]) }}";
                        throw new Error('Session expired');
                    }
                    if (!res.ok) throw new Error('Network error');
                    return res.json();
                })
                .then(data => {
                    const toggleBtn = document.getElementById('notifDropdownToggle');
                    let badgeCount = document.getElementById('notifBadgeCount');

                    if (data.unread_count > 0) {
                        if (!badgeCount && toggleBtn) {
                            badgeCount = document.createElement('span');
                            badgeCount.className = 'notification-badge';
                            badgeCount.id = 'notifBadgeCount';
                            toggleBtn.appendChild(badgeCount);
                        }
                        if (badgeCount) {
                            badgeCount.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                        }
                        const statusBadge = document.getElementById('notifStatusBadge');
                        if (statusBadge) {
                            statusBadge.className = 'badge bg-danger-subtle text-danger rounded-pill';
                            statusBadge.textContent = data.unread_count + ' Perlu Perhatian';
                        }
                    } else {
                        if (badgeCount) badgeCount.remove();
                        const statusBadge = document.getElementById('notifStatusBadge');
                        if (statusBadge) {
                            statusBadge.className = 'badge bg-secondary-subtle text-muted rounded-pill';
                            statusBadge.textContent = 'Semua Terpantau';
                        }
                    }
                })
                .catch(err => {});
            }, 30000);
            @endauth
        });
    </script>

    @stack('scripts')
</body>
</html>
<!-- Synchronized: Bank Mini SMKN 1 Bantul Complete Layout (v2.1) -->