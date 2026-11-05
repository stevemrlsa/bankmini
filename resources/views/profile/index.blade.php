@extends('layouts.app')

@section('title', 'Profil Pengguna — Bank Mini')
@section('header_title', 'Profil Pengguna')
@section('page-title', 'Profil & Keamanan Akun')
@section('page-subtitle', 'Kelola informasi identitas, ganti kata sandi, dan perbarui alamat email dengan verifikasi kode OTP')

@section('content')
<div class="container-fluid px-0">

    {{-- Notifikasi Sukses / Error / Notice --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 14px;" role="alert">
            <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center mb-4" style="border-radius: 14px;" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('dev_otp_notice'))
        <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 14px; background-color: #FEF3C7; color: #92400E;" role="alert">
            <div class="d-flex align-items-start">
                <i class="bi bi-info-circle-fill fs-5 me-2 flex-shrink-0 mt-1 text-warning"></i>
                <div>
                    <strong>Informasi Mode Pengujian:</strong><br>
                    {{ session('dev_otp_notice') }}
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" style="border-radius: 14px;" role="alert">
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

    <!-- Profile Header Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 20px; overflow: hidden; background: linear-gradient(135deg, #16233F 0%, #24355A 100%); color: #FFFFFF;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <div class="d-flex align-items-center justify-content-center rounded-circle fw-bold shadow" 
                     style="width: 76px; height: 76px; background: #FFFFFF; color: #16233F; font-size: 2rem; border: 3px solid rgba(255,255,255,0.4);">
                    {{ strtoupper(substr($user->officer_name ?? $user->username ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h4 class="fw-bold mb-0 text-white">{{ $user->officer_name ?? $user->username }}</h4>
                        <span class="badge bg-light text-dark px-3 py-1 rounded-pill fw-semibold" style="font-size: 0.78rem;">
                            <i class="bi bi-shield-lock-fill text-primary me-1"></i>{{ $user->role ?? 'Pegawai' }}
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill fw-semibold" style="font-size: 0.75rem;">
                            <i class="bi bi-check-circle-fill me-1"></i>{{ $user->status ?? 'Aktif' }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-3 text-white-50 small flex-wrap">
                        <span><i class="bi bi-person me-1"></i>Username: <strong>{{ $user->username }}</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-envelope me-1"></i>Email: <strong>{{ $user->email ?: 'Belum diatur' }}</strong></span>
                        <span>•</span>
                        <span><i class="bi bi-calendar-check me-1"></i>Terdaftar sejak: {{ $user->created_at ? $user->created_at->translatedFormat('d F Y') : '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <ul class="nav nav-pills mb-4 gap-2" id="profileTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ (!session('profile_pw_otp_sent') && !session('change_email_otp_sent')) ? 'active' : '' }} rounded-pill px-4 py-2 fw-semibold" 
                    id="info-tab" data-bs-toggle="tab" data-bs-target="#tab-info" type="button" role="tab">
                <i class="bi bi-person-badge me-1"></i> Informasi Lengkap
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ session('profile_pw_otp_sent') ? 'active' : '' }} rounded-pill px-4 py-2 fw-semibold" 
                    id="password-tab" data-bs-toggle="tab" data-bs-target="#tab-password" type="button" role="tab">
                <i class="bi bi-key-fill me-1"></i> Ganti Kata Sandi (OTP)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ session('change_email_otp_sent') ? 'active' : '' }} rounded-pill px-4 py-2 fw-semibold" 
                    id="email-tab" data-bs-toggle="tab" data-bs-target="#tab-email" type="button" role="tab">
                <i class="bi bi-envelope-at-fill me-1"></i> Ganti Alamat Email (OTP)
            </button>
        </li>
    </ul>

    <!-- Tab Contents -->
    <div class="tab-content" id="profileTabContent">

        <!-- TAB 1: INFORMASI LENGKAP -->
        <div class="tab-pane fade {{ (!session('profile_pw_otp_sent') && !session('change_email_otp_sent')) ? 'show active' : '' }}" id="tab-info" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-info-circle text-primary me-2"></i>Data Identitas Akun</h5>
                    <small class="text-muted">Informasi resmi akun Anda yang terdaftar pada sistem Bank Mini SMKN 1 Bantul</small>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Nama Lengkap Petugas</label>
                            <input type="text" class="form-control" value="{{ $user->officer_name ?? $user->username }}" readonly style="background-color: #F8FAFC;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Username Login</label>
                            <input type="text" class="form-control" value="{{ $user->username }}" readonly style="background-color: #F8FAFC;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Peran / Hak Akses (Role)</label>
                            <input type="text" class="form-control" value="{{ $user->role }}" readonly style="background-color: #F8FAFC;">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Alamat Email Terdaftar</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                <input type="email" class="form-control" value="{{ $user->email ?: 'Belum diatur' }}" readonly style="background-color: #F8FAFC;">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Nomor WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-whatsapp text-success"></i></span>
                                <input type="text" class="form-control" value="{{ $user->whatsapp_number ?: '-' }}" readonly style="background-color: #F8FAFC;">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted small fw-semibold">Status Keanggotaan</label>
                            <input type="text" class="form-control" value="Aktif (Terverifikasi)" readonly style="background-color: #F8FAFC;">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB 2: GANTI KATA SANDI (VIA OTP DENGAN TIMER 2 MENIT) -->
        <div class="tab-pane fade {{ session('profile_pw_otp_sent') ? 'show active' : '' }}" id="tab-password" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-shield-lock text-primary me-2"></i>Perbarui Kata Sandi Akun</h5>
                    <small class="text-muted">Untuk menjaga keamanan, kode OTP akan dikirimkan ke email terdaftar Anda (<strong>{{ $user->email }}</strong>)</small>
                </div>
                <div class="card-body p-4">

                    @if(!session('profile_pw_otp_sent'))
                        {{-- Langkah 1: Minta Kode OTP --}}
                        <div class="text-center py-4" style="max-width: 500px; margin: 0 auto;">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" 
                                 style="width: 64px; height: 64px; background: #EFF6FF; color: #1E3A8A;">
                                <i class="bi bi-send-check fs-2"></i>
                            </div>
                            <h5 class="fw-bold text-dark">Kirimkan Kode OTP Keamanan</h5>
                            <p class="text-muted small">
                                Sistem akan mengirimkan 6-digit kode OTP ke alamat email terdaftar Anda: <br>
                                <strong class="text-primary">{{ $user->email }}</strong>. <br>
                                Kode OTP ini memiliki batas waktu kedaluwarsa <strong>1 hingga 2 menit</strong>.
                            </p>

                            <form method="POST" action="{{ route('profile.password.otp') }}">
                                @csrf
                                <button type="submit" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold shadow-sm" style="background-color: #111827; border: none;">
                                    <i class="bi bi-envelope-paper me-1"></i> Kirim Kode OTP Sekarang
                                </button>
                            </form>
                        </div>
                    @else
                        {{-- Langkah 2: Masukkan Kode OTP & Password Baru dengan Countdown Timer 2 Menit --}}
                        <div style="max-width: 520px; margin: 0 auto;">
                            
                            {{-- Countdown Timer Box --}}
                            <div class="p-3 mb-4 text-center rounded-3 border" style="background-color: #F8FAFC;">
                                <small class="text-muted d-block mb-1">Batas waktu aktif kode OTP ganti sandi:</small>
                                <span id="pwCountdownBadge" class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 font-monospace fs-6 fw-bold">
                                    <i class="bi bi-stopwatch me-1"></i> <span id="pwTimerDisplay">02:00</span>
                                </span>
                                <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Jika waktu habis, Anda harus meminta kode OTP ulang.</small>
                            </div>

                            <form method="POST" action="{{ route('profile.password.update') }}">
                                @csrf

                                {{-- Input Kode OTP --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">Masukkan 6 Digit Kode OTP <span class="text-danger">*</span></label>
                                    <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                           class="form-control text-center font-monospace fs-5 fw-bold" placeholder="••••••" required autofocus>
                                    <div class="form-text">Periksa kotak masuk (Inbox atau Spam) email {{ $user->email }}.</div>
                                </div>

                                {{-- Password Baru --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">Kata Sandi Baru <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                                </div>

                                {{-- Konfirmasi Password Baru --}}
                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-dark">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ketik ulang kata sandi baru" required>
                                </div>

                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold shadow-sm" style="background-color: #111827; border: none;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Kata Sandi Baru
                                    </button>

                                    <form method="POST" action="{{ route('profile.password.otp') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-link text-decoration-none small text-muted">
                                            <i class="bi bi-arrow-repeat me-1"></i> Kirim Ulang OTP
                                        </button>
                                    </form>
                                </div>
                            </form>

                            {{-- Script Timer 2 Menit --}}
                            <script>
                                (function() {
                                    var expiresAt = {{ session('profile_pw_otp_expires_at', time() + 120) }} * 1000;
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

        <!-- TAB 3: GANTI ALAMAT EMAIL (VERIFIKASI PASSWORD SAAT INI + OTP KE EMAIL BARU DENGAN TIMER 2 MENIT) -->
        <div class="tab-pane fade {{ session('change_email_otp_sent') ? 'show active' : '' }}" id="tab-email" role="tabpanel">
            <div class="card border-0 shadow-sm" style="border-radius: 20px; background: #ffffff;">
                <div class="card-header bg-white border-bottom py-3 px-4">
                    <h5 class="fw-bold mb-1 text-dark"><i class="bi bi-envelope-check text-primary me-2"></i>Perbarui Alamat Email Akun</h5>
                    <small class="text-muted">Masukkan email baru dan kata sandi saat ini. Kode OTP akan dikirim ke alamat email baru untuk memverifikasi kepemilikan.</small>
                </div>
                <div class="card-body p-4">

                    @if(!session('change_email_otp_sent'))
                        {{-- Langkah 1: Input Email Baru & Password Saat Ini --}}
                        <div style="max-width: 520px; margin: 0 auto;">
                            <div class="alert alert-info py-2 px-3 small rounded-3 mb-4 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill fs-5 me-2 text-primary"></i>
                                <div>
                                    Email saat ini: <strong>{{ $user->email ?: 'Belum diatur' }}</strong>.<br>
                                    Demi keamanan, Anda wajib memasukkan kata sandi saat ini sebelum sistem mengirimkan kode OTP ke email baru.
                                </div>
                            </div>

                            <form method="POST" action="{{ route('profile.email.otp') }}">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-dark">Alamat Email Baru <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope-at text-muted"></i></span>
                                        <input type="email" name="new_email" class="form-control" placeholder="contoh: nama.baru@gmail.com" required value="{{ old('new_email') }}">
                                    </div>
                                    <div class="form-text">Pastikan Anda memiliki akses aktif ke email baru tersebut untuk membuka kode OTP.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-dark">Kata Sandi Akun Saat Ini <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-shield-lock text-muted"></i></span>
                                        <input type="password" name="current_password" class="form-control" placeholder="Masukkan kata sandi login Anda" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold shadow-sm" style="background-color: #111827; border: none;">
                                    <i class="bi bi-send-fill me-1"></i> Kirim Kode OTP ke Email Baru
                                </button>
                            </form>
                        </div>
                    @else
                        {{-- Langkah 2: Input Kode OTP dari Email Baru dengan Countdown Timer 2 Menit --}}
                        <div style="max-width: 520px; margin: 0 auto;">
                            
                            <div class="alert alert-success py-2 px-3 small rounded-3 mb-3 d-flex align-items-center">
                                <i class="bi bi-envelope-check-fill fs-5 me-2 text-success"></i>
                                <div>
                                    Kode OTP verifikasi telah dikirimkan ke: <br>
                                    <strong>{{ session('change_email_new') }}</strong>
                                </div>
                            </div>

                            {{-- Countdown Timer Box --}}
                            <div class="p-3 mb-4 text-center rounded-3 border" style="background-color: #F8FAFC;">
                                <small class="text-muted d-block mb-1">Batas waktu aktif kode OTP verifikasi email baru:</small>
                                <span id="emailCountdownBadge" class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 font-monospace fs-6 fw-bold">
                                    <i class="bi bi-stopwatch me-1"></i> <span id="emailTimerDisplay">02:00</span>
                                </span>
                                <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">Waktu sistem: 1 sampai 2 menit. Jika lewat, wajib request ulang.</small>
                            </div>

                            <form method="POST" action="{{ route('profile.email.update') }}">
                                @csrf

                                <div class="mb-4">
                                    <label class="form-label fw-semibold small text-dark">Masukkan 6 Digit Kode OTP dari Email Baru <span class="text-danger">*</span></label>
                                    <input type="text" name="otp" maxlength="6" pattern="[0-9]{6}" inputmode="numeric" 
                                           class="form-control text-center font-monospace fs-5 fw-bold" placeholder="••••••" required autofocus>
                                </div>

                                <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-dark rounded-pill px-4 py-2 fw-semibold shadow-sm" style="background-color: #111827; border: none;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Verifikasi &amp; Simpan Email Baru
                                    </button>

                                    <a href="{{ route('profile.show') }}" class="btn btn-link text-decoration-none small text-muted">
                                        <i class="bi bi-x-circle me-1"></i> Batal
                                    </a>
                                </div>
                            </form>

                            {{-- Script Timer 2 Menit --}}
                            <script>
                                (function() {
                                    var expiresAt = {{ session('change_email_expires_at', time() + 120) }} * 1000;
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

</div>
@endsection

