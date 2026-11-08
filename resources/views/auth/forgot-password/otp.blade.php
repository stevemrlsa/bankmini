<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi OTP — Bank Mini SMKN 1 Bantul</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-smkn1bantul.png') }}">

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
            --primary-navy-dark: #0E1830;
            --primary-navy-light: #24355A;
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --input-border: #CBD5E1;
            --bg-page: #F8FAFC;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-page);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== Container Utama (Split Card 2 Panel) ===== */
        .login-wrapper {
            width: 100%;
            max-width: 890px;
            min-height: 520px;
            background: #FFFFFF;
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.10), 0 4px 16px rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            overflow: hidden;
            position: relative;
        }

        /* ===== Panel Kiri: Biru Dongker + Logo SMKN 1 Bantul ===== */
        .brand-panel {
            flex: 0 0 45%;
            max-width: 45%;
            background: linear-gradient(180deg, var(--primary-navy) 0%, var(--primary-navy-dark) 100%);
            color: #FFFFFF;
            padding: 48px 36px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .brand-panel::before {
            content: '';
            position: absolute;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.08) 0%, transparent 70%);
            top: -60px;
            left: -60px;
            pointer-events: none;
        }

        .brand-panel::after {
            content: '';
            position: absolute;
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.08) 0%, transparent 70%);
            bottom: -60px;
            right: -60px;
            pointer-events: none;
        }

        .brand-logo-wrap {
            position: relative;
            z-index: 1;
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 145px;
            height: 145px;
            object-fit: contain;
            filter: drop-shadow(0 10px 22px rgba(0, 0, 0, 0.30));
            transition: transform 0.3s ease;
        }

        .brand-logo:hover {
            transform: scale(1.04);
        }

        .welcome-title {
            position: relative;
            z-index: 1;
            font-size: 1.55rem;
            font-weight: 700;
            color: #FFFFFF;
            margin-bottom: 8px;
            letter-spacing: -0.01em;
        }

        .welcome-subtitle {
            position: relative;
            z-index: 1;
            font-size: 0.88rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.55;
            max-width: 270px;
        }

        /* ===== Panel Kanan: Form Putih Elegan ===== */
        .form-panel {
            flex: 0 0 55%;
            max-width: 55%;
            background: #FFFFFF;
            padding: 46px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-box-inner {
            border: 1.5px solid #F1F5F9;
            border-radius: 22px;
            padding: 34px 30px;
            background: #FFFFFF;
            box-shadow: 0 4px 20px rgba(15, 23, 42, 0.02);
        }

        .form-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .form-title {
            font-size: 1.65rem;
            font-weight: 800;
            color: var(--primary-navy);
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }

        .form-subtitle {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.45;
        }

        .field-group {
            margin-bottom: 22px;
        }

        .field-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 8px;
            text-align: center;
        }

        .otp-input {
            width: 100%;
            height: 56px;
            font-family: 'Poppins', sans-serif;
            font-size: 1.6rem;
            font-weight: 700;
            text-align: center;
            letter-spacing: 0.6rem;
            color: var(--primary-navy);
            background: #F8FAFC;
            border: 2px solid var(--input-border);
            border-radius: 14px;
            outline: none;
            transition: all 0.2s ease;
        }

        .otp-input::placeholder {
            color: #CBD5E1;
            font-weight: 400;
            letter-spacing: 0.5rem;
        }

        .otp-input:focus {
            background: #FFFFFF;
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 4px rgba(22, 35, 63, 0.12);
        }

        .btn-action {
            width: 100%;
            height: 48px;
            background-color: var(--primary-navy);
            color: #FFFFFF;
            border: none;
            border-radius: 12px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.02em;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 6px 18px rgba(22, 35, 63, 0.20);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-action:hover {
            background-color: var(--primary-navy-dark);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(22, 35, 63, 0.28);
            color: #FFFFFF;
        }

        .btn-action:active {
            transform: translateY(0);
        }

        .links-group {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 20px;
            font-size: 0.8rem;
            flex-wrap: wrap;
            gap: 8px;
        }

        .links-group a {
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: color 0.18s ease;
        }

        .links-group a:hover {
            color: var(--primary-navy);
        }

        /* ===== Responsive Breakpoint ===== */
        @media (max-width: 768px) {
            .login-wrapper {
                flex-direction: column;
                max-width: 440px;
                min-height: auto;
            }

            .brand-panel {
                flex: 1 1 100%;
                max-width: 100%;
                padding: 36px 24px 30px;
            }

            .brand-logo {
                width: 95px;
                height: 95px;
            }

            .welcome-title {
                font-size: 1.35rem;
            }

            .welcome-subtitle {
                font-size: 0.82rem;
                max-width: 100%;
            }

            .form-panel {
                flex: 1 1 100%;
                max-width: 100%;
                padding: 30px 20px 34px;
            }

            .login-box-inner {
                padding: 24px 18px;
                border-radius: 18px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        <!-- ===== Panel Kiri: Brand SMKN 1 Bantul ===== -->
        <div class="brand-panel">
            <div class="brand-logo-wrap">
                <img src="{{ asset('images/logo-smkn1bantul.png') }}" alt="Logo SMKN 1 Bantul" class="brand-logo">
            </div>
            <h2 class="welcome-title">Verifikasi Akun</h2>
            <p class="welcome-subtitle">Selamat Datang di sistem bank mini smkn 1 bantul</p>
        </div>

        <!-- ===== Panel Kanan: Form OTP ===== -->
        <div class="form-panel">
            <div class="login-box-inner">

                <div class="form-header">
                    <h1 class="form-title">Verifikasi OTP</h1>
                    <p class="form-subtitle">Masukkan 6 digit kode OTP yang telah dikirimkan ke alamat email Anda.</p>
                </div>

                @if (session('success'))
                    <div class="alert alert-success py-2 px-3 mb-3 d-flex align-items-center" style="border-radius: 10px; font-size: 0.8rem; border: 1px solid #BBF7D0; background-color: #DCFCE7; color: #166534;">
                        <i class="bi bi-check-circle-fill me-2 fs-6"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center" style="border-radius: 10px; font-size: 0.8rem; border: 1px solid #FECACA; background-color: #FEE2E2; color: #991B1B;">
                        <i class="bi bi-exclamation-triangle-fill me-2 fs-6"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if (session('dev_otp_notice'))
                    <div class="alert alert-warning py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.78rem; background-color: #FEF3C7; border: 1px solid #FDE68A; color: #92400E;">
                        <div class="d-flex align-items-start">
                            <i class="bi bi-info-circle-fill me-2 fs-6 text-warning flex-shrink-0 mt-1"></i>
                            <div>
                                <strong>Info Mode Pengujian:</strong><br>
                                {{ session('dev_otp_notice') }}
                            </div>
                        </div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.8rem; border: 1px solid #FECACA; background-color: #FEE2E2; color: #991B1B;">
                        <ul class="mb-0" style="padding-left: 1rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.otp.verify') }}">
                    @csrf

                    <div class="field-group">
                        <label class="field-label" for="otp">Masukkan 6 Digit Angka OTP</label>
                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            maxlength="6"
                            pattern="[0-9]{6}"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            class="otp-input"
                            placeholder="••••••"
                            required
                            autofocus
                        >
                    </div>

                    <div class="text-center my-3 p-3 rounded-3 border" style="background-color: #F8FAFC;">
                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">Batas waktu verifikasi kode OTP (2 menit):</small>
                        <span id="countdownBadge" class="badge bg-light text-danger border px-3 py-1 font-monospace fw-bold" style="font-size: 0.88rem; border-radius: 8px;">
                            <i class="bi bi-stopwatch me-1"></i> <span id="timerDisplay">02:00</span>
                        </span>
                        @if(session('reset_email'))
                            <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Dikirim ke: <strong>{{ session('reset_email') }}</strong></small>
                        @endif
                    </div>

                    <button type="submit" class="btn-action">
                        <i class="bi bi-shield-check"></i> Verifikasi Kode OTP
                    </button>
                </form>

                <!-- Tombol Kirim Ulang Kode OTP (Selalu aktif sebelum maupun sesudah waktu habis) -->
                <form method="POST" action="{{ route('password.otp.resend') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-2" style="height: 44px; border-radius: 12px; font-size: 0.88rem;">
                        <i class="bi bi-arrow-repeat"></i> Kirim Ulang Kode OTP
                    </button>
                </form>

                <script>
                    (function() {
                        var expiresAt = {{ session('reset_otp_expires_at', time() + 120) }} * 1000;
                        var timerDisplay = document.getElementById('timerDisplay');
                        var countdownBadge = document.getElementById('countdownBadge');

                        function updateTimer() {
                            var now = new Date().getTime();
                            var distance = expiresAt - now;

                            if (distance <= 0) {
                                if (timerDisplay) timerDisplay.innerText = "00:00 (Kedaluwarsa)";
                                if (countdownBadge) countdownBadge.className = "badge bg-danger text-white px-3 py-1 font-monospace fw-bold";
                                clearInterval(interval);
                                return;
                            }

                            var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                            var seconds = Math.floor((distance % (1000 * 60)) / 1000);
                            var mStr = (minutes < 10 ? "0" : "") + minutes;
                            var sStr = (seconds < 10 ? "0" : "") + seconds;

                            if (timerDisplay) timerDisplay.innerText = mStr + ":" + sStr;
                        }

                        updateTimer();
                        var interval = setInterval(updateTimer, 1000);
                    })();
                </script>

                <div class="links-group">
                    <a href="{{ route('password.request') }}">
                        <i class="bi bi-envelope-at"></i> Ganti Email Penerima
                    </a>
                    <a href="{{ route('login') }}">
                        <i class="bi bi-arrow-left"></i> Kembali ke Login
                    </a>
                </div>

            </div>
        </div>

    </div>

</body>
</html>
