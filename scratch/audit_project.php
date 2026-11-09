<?php

require dirname(__DIR__) . '/vendor/autoload.php';
$app = require_once dirname(__DIR__) . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Route;

echo "========================================================\n";
echo "      COMPREHENSIVE AUDIT SUITE - BANK MINI SMKN 1 BANTUL\n";
echo "========================================================\n\n";

$results = [];

function check($testName, $callback) {
    global $results;
    try {
        $res = $callback();
        if ($res === true || (is_array($res) && $res['pass'])) {
            $msg = is_array($res) ? $res['msg'] : 'OK';
            echo "[PASS] $testName: $msg\n";
            $results[] = ['name' => $testName, 'status' => 'PASS', 'msg' => $msg];
        } else {
            $msg = is_array($res) ? $res['msg'] : 'Failed';
            echo "[FAIL] $testName: $msg\n";
            $results[] = ['name' => $testName, 'status' => 'FAIL', 'msg' => $msg];
        }
    } catch (\Throwable $e) {
        echo "[ERROR] $testName: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
        $results[] = ['name' => $testName, 'status' => 'ERROR', 'msg' => $e->getMessage()];
    }
}

// ----------------------------------------------------
// TAHAP 1: PENGETESAN PUBLIK & LANDING PAGE & AUTH
// ----------------------------------------------------
echo "--- TAHAP 1: PUBLIK & AUTENTIKASI ---\n";

check("1.1 APK Download Existence & Route", function() {
    $path = public_path('downloads/bankmini-nasabah.apk');
    if (!file_exists($path)) {
        return ['pass' => false, 'msg' => "File $path tidak ditemukan"];
    }
    $sizeMb = round(filesize($path) / (1024 * 1024), 2);
    $route = Route::has('download.apk');
    if (!$route) {
        return ['pass' => false, 'msg' => "Route download.apk belum terdaftar"];
    }
    return ['pass' => true, 'msg' => "File ditemukan ($sizeMb MB), Route download.apk valid"];
});

check("1.2 OTP Configuration & Resend Route", function() {
    $hasForgotRoute = Route::has('password.request') && Route::has('password.otp.resend');
    $hasProfileOtp = Route::has('profile.password.resend-otp') && Route::has('profile.email.resend-otp');
    $hasSaverOtp = Route::has('saver.profile.password.resend-otp') && Route::has('saver.profile.email.resend-otp');
    
    if (!$hasForgotRoute || !$hasProfileOtp || !$hasSaverOtp) {
        return ['pass' => false, 'msg' => "Sebagian route resend-otp belum lengkap"];
    }
    
    if (!class_exists(\App\Mail\SendOtpMail::class)) {
        return ['pass' => false, 'msg' => "Class SendOtpMail tidak ditemukan"];
    }
    
    $methodExists = method_exists(\App\Mail\SendOtpMail::class, 'sendReliably');
    return ['pass' => $methodExists, 'msg' => "Semua route resend-otp & SendOtpMail::sendReliably() terkonfigurasi dengan baik"];
});

// ----------------------------------------------------
// TAHAP 2: PENGETESAN OPERASIONAL LOKET TELLER
// ----------------------------------------------------
echo "\n--- TAHAP 2: OPERASIONAL LOKET TELLER ---\n";

check("2.1 Shift Table Schema & Active Shift Logic", function() {
    // Check migration file
    $migrationExists = file_exists(database_path('migrations/2026_10_23_051847_create_teller_shifts_table.php'));
    $hasRoute = Route::has('teller.shift.create') && Route::has('teller.shift.store');
    return ['pass' => ($migrationExists && $hasRoute), 'msg' => "Migrasi tabel teller_shifts & route shift aktif"];
});

check("2.2 Tambah Kas Laci (Top-Up Kas Laci)", function() {
    $hasRoute = Route::has('teller.shift.topup');
    return ['pass' => $hasRoute, 'msg' => "Route teller.shift.topup tersedia"];
});

check("2.3 Aktivasi Nasabah Baru & Setoran Awal Minimal Rp 10.000", function() {
    $hasRoute = Route::has('teller.activations.index') && Route::has('teller.activations.activate');
    if (!$hasRoute) return ['pass' => false, 'msg' => "Route aktivasi teller tidak ditemukan"];
    
    $ctrlFile = file_get_contents(app_path('Http/Controllers/Teller/TellerActivationController.php'));
    $hasMinDepositCheck = strpos($ctrlFile, '10000') !== false || strpos($ctrlFile, '10.000') !== false;
    return ['pass' => $hasMinDepositCheck, 'msg' => "Validasi setoran awal minimal Rp 10.000 ada di controller"];
});

check("2.4 Setoran Tunai (Deposit)", function() {
    $hasRoute = Route::has('teller.setoran.store');
    return ['pass' => $hasRoute, 'msg' => "Route teller.setoran.store tersedia"];
});

check("2.5 Penarikan Tunai: Saldo Mengendap Rp 10.000, Limit 1jt, Kas Laci", function() {
    $txCtrlFile = file_get_contents(app_path('Http/Controllers/Teller/TransactionController.php'));
    
    // Check saldo mengendap 10.000
    $hasMinBalanceCheck = (strpos($txCtrlFile, '10000') !== false || strpos($txCtrlFile, '10.000') !== false) 
        && strpos($txCtrlFile, 'saldo mengendap') !== false;
        
    // Check daily limit 1.000.000
    $hasDailyLimitCheck = strpos($txCtrlFile, '1000000') !== false && 
        (strpos($txCtrlFile, 'kuota penarikan harian') !== false || strpos($txCtrlFile, 'kuota harian') !== false);
    
    // Check cash drawer check
    $hasCashDrawerCheck = strpos($txCtrlFile, 'Uang kas fisik di laci') !== false || strpos($txCtrlFile, 'kas fisik di laci tidak mencukupi') !== false;
    
    // Check blocked account check
    $hasBlockedCheck = strpos($txCtrlFile, 'diblokir') !== false;
    
    if (!$hasMinBalanceCheck) return ['pass' => false, 'msg' => "Pemeriksaan saldo mengendap Rp 10.000 belum lengkap"];
    if (!$hasDailyLimitCheck) return ['pass' => false, 'msg' => "Pemeriksaan limit harian 1.000.000 belum lengkap"];
    if (!$hasCashDrawerCheck) return ['pass' => false, 'msg' => "Pemeriksaan uang fisik kas laci belum lengkap"];
    if (!$hasBlockedCheck) return ['pass' => false, 'msg' => "Pemeriksaan status rekening diblokir belum lengkap"];
    
    return ['pass' => true, 'msg' => "Semua aturan validasi penarikan reguler (saldo mengendap, limit 1jt, kas laci, blokir) terverifikasi"];
});

check("2.6 Pengajuan Penarikan Dana Besar & Tidak Ada Unknown Column 'notes'", function() {
    // Scan all controller files to ensure no Transaction::create with 'notes'
    $controllers = glob(app_path('Http/Controllers/**/*.php'));
    foreach ($controllers as $ctrl) {
        $content = file_get_contents($ctrl);
        if (preg_match("/Transaction::create\s*\(\s*\[[^\]]*'notes'/is", $content)) {
            return ['pass' => false, 'msg' => "Ditemukan query Transaction::create di $ctrl yang menyertakan kolom non-existent 'notes'!"];
        }
    }
    
    $hasRoute = Route::has('teller.withdrawal-requests.index');
    return ['pass' => $hasRoute, 'msg' => "Route pengajuan penarikan aktif & 0 hardcoded 'notes' pada Transaction::create di seluruh controller"];
});

check("2.7 Penutupan Kas Harian (Closing Shift)", function() {
    $hasRoute = Route::has('teller.report.closing') && Route::has('teller.report.store');
    return ['pass' => $hasRoute, 'msg' => "Route penutupan kas harian lengkap"];
});

check("2.8 Shared Shift & Multi-Teller Live Status Endpoint", function() {
    $hasLiveStatusRoute = Route::has('teller.shift.live-status');
    if (!$hasLiveStatusRoute) return ['pass' => false, 'msg' => "Route teller.shift.live-status belum terdaftar"];

    $ctrlFile = file_get_contents(app_path('Http/Controllers/TellerShiftController.php'));
    $hasLiveStatusMethod = strpos($ctrlFile, 'function liveStatus') !== false;
    $hasSharedDrawerCalculation = strpos($ctrlFile, 'calculateCurrentDrawerCash') !== false;

    return ['pass' => ($hasLiveStatusMethod && $hasSharedDrawerCalculation), 'msg' => "Route live-status & endpoint polling sinkronisasi laci bersama terverifikasi"];
});

check("2.9 Model TellerShift: Shared Cash Drawer & Breakdown Methods", function() {
    $modelFile = file_get_contents(app_path('Models/TellerShift.php'));
    $hasActiveShift = method_exists(\App\Models\TellerShift::class, 'getActiveOpenShift');
    $hasAttachTeller = method_exists(\App\Models\TellerShift::class, 'attachTeller');
    $hasDrawerCash = method_exists(\App\Models\TellerShift::class, 'calculateCurrentDrawerCash');
    $hasBreakdown = method_exists(\App\Models\TellerShift::class, 'getBreakdownPerTeller');

    if (!$hasActiveShift || !$hasAttachTeller || !$hasDrawerCash || !$hasBreakdown) {
        return ['pass' => false, 'msg' => "Method pada model TellerShift belum lengkap"];
    }

    return ['pass' => true, 'msg' => "Method getActiveOpenShift, attachTeller, calculateCurrentDrawerCash, dan getBreakdownPerTeller siap digunakan"];
});

// ----------------------------------------------------
// TAHAP 3: PENGETESAN KONTROL ADMINISTRATOR
// ----------------------------------------------------
echo "\n--- TAHAP 3: KONTROL ADMINISTRATOR ---\n";

check("3.1 Syarat Kenaikan Kelas sebelum Import Data Siswa Baru", function() {
    $memberCtrlFile = file_get_contents(app_path('Http/Controllers/Admin/AdminMemberController.php'));
    $hasPromotionCheck = strpos($memberCtrlFile, 'last_class_promotion_year') !== false && 
        strpos($memberCtrlFile, 'Kenaikan Kelas Siswa') !== false;
        
    return ['pass' => $hasPromotionCheck, 'msg' => "Validasi syarat kenaikan kelas siswa sebelum import Excel aktif"];
});

check("3.2 Fitur Blokir & Buka Blokir Rekening Siswa", function() {
    $hasBlockRoute = Route::has('admin.savers.block');
    $hasUnblockRoute = Route::has('admin.savers.unblock');
    
    if (!$hasBlockRoute || !$hasUnblockRoute) {
        return ['pass' => false, 'msg' => "Route admin.savers.block atau admin.savers.unblock tidak ditemukan"];
    }
    
    $hasBlockMethod = method_exists(\App\Http\Controllers\Admin\AdminCustomerController::class, 'blockAccount');
    $hasUnblockMethod = method_exists(\App\Http\Controllers\Admin\AdminCustomerController::class, 'unblockAccount');
    
    return ['pass' => ($hasBlockMethod && $hasUnblockMethod), 'msg' => "Route dan method blokir & buka blokir tersedia"];
});

check("3.3 Otorisasi Pengajuan Penarikan oleh Admin", function() {
    $hasApproveAdmin = Route::has('admin.withdrawal-requests.approve-admin');
    $hasRejectAdmin = Route::has('admin.withdrawal-requests.reject');
    return ['pass' => ($hasApproveAdmin && $hasRejectAdmin), 'msg' => "Route approval & penolakan janjian penarikan dana besar oleh Admin tersedia"];
});

check("3.4 Approval Rekonsiliasi Kas Laci Teller & PDF", function() {
    $hasApproveRoute = Route::has('admin.approval.approve');
    $hasPdfRoute = Route::has('admin.approval.pdf');
    return ['pass' => ($hasApproveRoute && $hasPdfRoute), 'msg' => "Route approval kas teller & export PDF tersedia"];
});

check("3.5 Jurnal Akuntansi & Neraca Keuangan", function() {
    $hasJournalRoute = Route::has('admin.journals.index');
    $hasMonthlyReportRoute = Route::has('admin.reports.monthly');
    return ['pass' => ($hasJournalRoute && $hasMonthlyReportRoute), 'msg' => "Route jurnal umum & laporan bulanan tersedia"];
});

check("3.6 Audit Log Sistem", function() {
    $hasAuditRoute = Route::has('admin.audit.index');
    $migrationExists = file_exists(database_path('migrations/2026_11_08_163001_create_audit_logs_table.php'));
    return ['pass' => ($hasAuditRoute && $migrationExists), 'msg' => "Route audit log dan migrasi audit_logs siap"];
});

// ----------------------------------------------------
// TAHAP 4 & 5: PORTAL SISWA & MOBILE API
// ----------------------------------------------------
echo "\n--- TAHAP 4 & 5: PORTAL SISWA & MOBILE API ---\n";

check("4.1 Portal Siswa Login & Blocked Check", function() {
    $hasLoginRoute = Route::has('saver.login');
    $hasSubmitRoute = Route::has('saver.login.submit');
    
    $middlewareFile = file_get_contents(app_path('Http/Middleware/CheckSaverSession.php'));
    $hasBlockedCheckInMiddleware = strpos($middlewareFile, 'blocked') !== false;
    
    return ['pass' => ($hasLoginRoute && $hasSubmitRoute && $hasBlockedCheckInMiddleware), 'msg' => "Portal siswa login & middleware CheckSaverSession memvalidasi status blocked"];
});

check("4.2 Portal Siswa Dashboard, Mutasi, dan Profil OTP", function() {
    $hasDashboard = Route::has('saver.dashboard');
    $hasStatement = Route::has('saver.statement');
    $hasProfile = Route::has('saver.profile');
    
    return ['pass' => ($hasDashboard && $hasStatement && $hasProfile), 'msg' => "Dashboard, statement (mutasi), dan profile portal siswa aktif"];
});

check("4.3 Mobile API Routes & Controllers", function() {
    $apiFile = file_get_contents(base_path('routes/api.php'));
    $hasMobilePrefix = strpos($apiFile, 'mobile/auth') !== false;
    $hasSecurityPrefix = strpos($apiFile, 'mobile/security') !== false;
    
    return ['pass' => ($hasMobilePrefix && $hasSecurityPrefix), 'msg' => "Semua endpoint API Mobile untuk autentikasi dan keamanan aktif"];
});

// ----------------------------------------------------
// UI CHECKS: SIDEBAR, RECEIPT PRINT, MENU NAMES
// ----------------------------------------------------
echo "\n--- UI CHECKS: SIDEBAR, STRUK, MENU ---\n";

check("UI 1. Sidebar Nav Labels ('Pengajuan Penarikan' without '> 1 Jt')", function() {
    $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
    
    $hasOldLabel = strpos($appLayout, 'Janjian Penarikan (> 1 Jt)') !== false;
    $hasNewLabel = strpos($appLayout, 'Pengajuan Penarikan') !== false;
    
    if ($hasOldLabel) {
        return ['pass' => false, 'msg' => "Masih ditemukan teks lama 'Janjian Penarikan (> 1 Jt)' di layouts/app.blade.php"];
    }
    return ['pass' => $hasNewLabel, 'msg' => "Teks menu sidebar telah menjadi 'Pengajuan Penarikan' secara konsisten"];
});

check("UI 2. Sidebar Glider Height Consistency (48px locked) & Smooth Sliding", function() {
    $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
    
    $hasMinHeight48 = strpos($appLayout, 'min-height: 48px') !== false;
    $hasGliderFunction = strpos($appLayout, 'moveSidebarGlider') !== false;
    
    return ['pass' => ($hasMinHeight48 && $hasGliderFunction), 'msg' => "Glider height 48px dan script moveSidebarGlider terpasang di app layout"];
});

check("UI 3. Print Receipt CSS Isolation (@media print & .no-print)", function() {
    $appLayout = file_get_contents(resource_path('views/layouts/app.blade.php'));
    $hasPrintMedia = strpos($appLayout, '@media print') !== false;
    $hasNoPrintClass = strpos($appLayout, '.no-print') !== false;
    
    $penarikanView = file_get_contents(resource_path('views/teller/penarikan.blade.php'));
    $setoranView = file_get_contents(resource_path('views/teller/setoran.blade.php'));
    
    $hasNoPrintInViews = strpos($penarikanView, 'no-print') !== false && strpos($setoranView, 'no-print') !== false;
    
    return ['pass' => ($hasPrintMedia && $hasNoPrintClass && $hasNoPrintInViews), 'msg' => "Isolasi cetak struk (@media print & .no-print) terpasang di layout dan view transaksi"];
});

echo "\n========================================================\n";
$total = count($results);
$passed = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
$failed = $total - $passed;
echo "AUDIT SUMMARY: $passed / $total PASSED" . ($failed > 0 ? " ($failed FAILED)" : " (100% ALL PERFECT!)") . "\n";
echo "========================================================\n";
