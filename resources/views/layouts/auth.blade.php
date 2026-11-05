<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Autentikasi — Bank Mini SMKN 1 Bantul')</title>
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
            --text-dark: #0F172A;
            --text-muted: #64748B;
            --border-soft: #EEF2F6;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            -webkit-font-smoothing: antialiased;
        }

        .auth-card {
            background: #FFFFFF;
            border-radius: 24px;
            box-shadow: 0 16px 40px rgba(12, 96, 240, 0.07);
            border: 1px solid var(--border-soft);
            padding: 38px 34px;
        }

        .form-control {
            border-radius: 14px;
            padding: 12px 16px;
            border: 1.5px solid #E2E8F0;
            font-size: 0.92rem;
            color: var(--text-dark);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(12, 96, 240, 0.12);
            outline: none;
        }

        .btn-custom-dark, .btn-primary {
            background-color: var(--primary) !important;
            color: #FFFFFF !important;
            border-radius: 50px;
            padding: 12px 24px;
            font-weight: 600;
            border: none;
            box-shadow: 0 6px 18px rgba(12, 96, 240, 0.25);
            transition: all 0.2s ease;
        }

        .btn-custom-dark:hover, .btn-primary:hover {
            background-color: var(--primary-hover) !important;
            color: #FFFFFF !important;
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(12, 96, 240, 0.35);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
