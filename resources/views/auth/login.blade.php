<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Bank Mini SMKN 1 Bantul</title>
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

        /* Subtle radial ambient light on navy panel */
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

        /* ===== Panel Kanan: Form Login Putih ===== */
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
            margin-bottom: 26px;
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
            margin-bottom: 18px;
        }

        .field-label {
            display: block;
            font-size: 0.82rem;
            font-weight: 600;
            color: #334155;
            margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-field {
            width: 100%;
            height: 46px;
            padding: 10px 14px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.88rem;
            color: var(--text-dark);
            background: #FFFFFF;
            border: 1.5px solid var(--input-border);
            border-radius: 12px;
            outline: none;
            transition: all 0.2s ease;
        }

        .input-field::placeholder {
            color: #94A3B8;
            font-weight: 400;
            font-size: 0.84rem;
        }

        .input-field:focus {
            border-color: var(--primary-navy);
            box-shadow: 0 0 0 3px rgba(22, 35, 63, 0.12);
        }

        .input-field.has-toggle {
            padding-right: 42px;
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            color: #94A3B8;
            cursor: pointer;
            font-size: 1.05rem;
            transition: color 0.18s ease;
            user-select: none;
        }

        .toggle-password:hover {
            color: var(--primary-navy);
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 22px;
            font-size: 0.82rem;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 0;
            padding: 0;
        }

        .form-check-input {
            width: 16px;
            height: 16px;
            margin: 0;
            border-radius: 4px;
            border: 1.5px solid #CBD5E1;
            cursor: pointer;
            accent-color: var(--primary-navy);
        }

        .form-check-label {
            color: #475569;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 400;
        }

        .forgot-link {
            color: #475569;
            text-decoration: none;
            font-size: 0.82rem;
            font-weight: 500;
            transition: color 0.18s ease;
        }

        .forgot-link:hover {
            color: var(--primary-navy);
            text-decoration: underline;
        }

        .btn-masuk {
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

        .btn-masuk:hover {
            background-color: var(--primary-navy-dark);
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(22, 35, 63, 0.28);
            color: #FFFFFF;
        }

        .btn-masuk:active {
            transform: translateY(0);
        }

        /* ===== Tombol Kembali ke Beranda ===== */
        .btn-kembali {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #64748B;
            font-size: 0.8rem;
            font-weight: 500;
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 50px;
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
            transition: all 0.18s ease;
            margin-bottom: 14px;
            width: fit-content;
        }

        .btn-kembali:hover {
            color: var(--primary-navy);
            background: #EFF6FF;
            border-color: #BFDBFE;
            transform: translateX(-2px);
        }

        .btn-kembali-bottom {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            height: 44px;
            background: #F8FAFC;
            color: #475569;
            border: 1.5px solid #E2E8F0;
            border-radius: 14px;
            font-size: 0.86rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.18s ease;
            margin-top: 10px;
        }

        .btn-kembali-bottom:hover {
            background: #F1F5F9;
            color: var(--primary-navy);
            border-color: #CBD5E1;
        }

        .footnote-link {
            text-align: center;
            margin-top: 16px;
        }

        .footnote-link a {
            font-size: 0.78rem;
            color: #64748B;
            text-decoration: none;
            transition: color 0.18s ease;
        }

        .footnote-link a:hover {
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
            <h2 class="welcome-title">Welcome back</h2>
            <p class="welcome-subtitle">Selamat Datang di sistem bank mini smkn 1 bantul</p>
        </div>

        <!-- ===== Panel Kanan: Form Login ===== -->
        <div class="form-panel">
            <div class="login-box-inner">

                <div class="form-header">
                    <h1 class="form-title">LOGIN</h1>
                    <p class="form-subtitle">Masuk sebagai Administrator, Teller, atau Siswa Nasabah</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.8rem; border: 1px solid #FECACA; background-color: #FEE2E2; color: #991B1B;">
                        <ul class="mb-0" style="padding-left: 1rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.8rem; border: 1px solid #FECACA; background-color: #FEE2E2; color: #991B1B;">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert py-2 px-3 mb-3" style="border-radius: 10px; font-size: 0.8rem; border: 1px solid #BBF7D0; background-color: #DCFCE7; color: #166534;">
                        <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="/login">
                    @csrf

                    {{-- Input Username / NIS / No Rekening --}}
                    <div class="field-group">
                        <label class="field-label" for="username">Username / NIS / No. Rekening</label>
                        <div class="input-wrap">
                            <input
                                type="text"
                                id="username"
                                name="username"
                                class="input-field"
                                placeholder="Masukkan username, NIS, atau no. rekening"
                                value="{{ old('username') }}"
                                autocomplete="username"
                                required
                                autofocus
                            >
                        </div>
                    </div>

                    {{-- Input Password / PIN --}}
                    <div class="field-group">
                        <label class="field-label" for="password">Password / PIN 6-Digit</label>
                        <div class="input-wrap">
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="input-field has-toggle"
                                placeholder="Masukkan password atau PIN keamanan"
                                autocomplete="current-password"
                                required
                            >
                            <i class="bi bi-eye-fill toggle-password" id="togglePassword" title="Lihat/Sembunyikan password"></i>
                        </div>
                    </div>

                    {{-- Opsi Ingat Saya & Lupa Password --}}
                    <div class="form-options">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember">
                            <label class="form-check-label" for="remember">Ingat saya</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">Lupa Password?</a>
                        @endif
                    </div>

                    {{-- Tombol Masuk --}}
                    <button type="submit" class="btn-masuk">
                        <i class="bi bi-box-arrow-in-right"></i> Masuk
                    </button>

                    {{-- Tombol Tambahan Kembali ke Beranda Awal --}}
                    <a href="{{ route('landing') }}" class="btn-kembali-bottom">
                        <i class="bi bi-house-door"></i> Kembali ke Beranda Awal
                    </a>
                </form>

            </div>
        </div>

    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        togglePassword?.addEventListener('click', function () {
            const isPassword = passwordInput.getAttribute('type') === 'password';
            passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
            this.classList.toggle('bi-eye-fill');
            this.classList.toggle('bi-eye-slash-fill');
        });
    </script>

</body>
</html>
