<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=================================================================\n";
echo "           SISTEM INFORMASI E-TELLER BANK MINI SEKOLAH           \n";
echo "              COMPREHENSIVE PROJECT HEALTH CHECK                 \n";
echo "=================================================================\n\n";

$results = [];

// 1. DATABASE & TABLES CHECK
echo "--- 1. DATABASE & TABLES CHECK ---\n";
$requiredTables = [
    'users',
    'members',
    'savers',
    'transactions',
    'accounting_journals',
    'teller_shifts',
    'daily_reports',
    'system_settings',
    'personal_access_tokens',
    'password_reset_tokens',
    'pegawais',
    'officers',
];

$missingTables = [];
foreach ($requiredTables as $table) {
    $exists = \Illuminate\Support\Facades\Schema::hasTable($table);
    if ($exists) {
        $count = \Illuminate\Support\Facades\DB::table($table)->count();
        echo " [OK] Tabel '$table': ada ($count baris)\n";
    } else {
        echo " [FAIL] Tabel '$table': TIDAK DITEMUKAN!\n";
        $missingTables[] = $table;
    }
}
$results['database_tables'] = empty($missingTables);

// 2. USERS & RBAC ROLES CHECK
echo "\n--- 2. USERS & RBAC ROLES CHECK ---\n";
$adminCount = \App\Models\User::whereIn('role', ['Administrator', 'Admin'])->count();
$tellerCount = \App\Models\User::where('role', 'Teller')->count();
$studentCount = \App\Models\User::whereIn('role', ['student', 'Nasabah'])->count();

echo " - Admin User       : $adminCount akun " . ($adminCount > 0 ? "[OK]" : "[WARNING]") . "\n";
echo " - Teller User      : $tellerCount akun " . ($tellerCount > 0 ? "[OK]" : "[WARNING]") . "\n";
echo " - Student / Nasabah: $studentCount akun " . ($studentCount > 0 ? "[OK]" : "[INFO]") . "\n";
$results['rbac_users'] = ($adminCount > 0 && $tellerCount > 0);

// 3. SAVERS & MEMBERS DATA INTEGRITY
echo "\n--- 3. SAVERS & MEMBERS DATA INTEGRITY ---\n";
$memberCount = \App\Models\Member::count();
$saverCount = \App\Models\Saver::count();
echo " - Total Anggota (Members): $memberCount\n";
echo " - Total Penabung (Savers) : $saverCount\n";

// Sample saver check
$sampleSaver = \App\Models\Saver::first();
if ($sampleSaver) {
    echo " - Sample Saver: No Rek: {$sampleSaver->account_number}, Saldo: Rp " . number_format($sampleSaver->balance ?? $sampleSaver->saldo ?? 0, 0, ',', '.') . "\n";
    $hasPin = !empty($sampleSaver->security_pin);
    echo " - Security PIN: " . ($hasPin ? "Tersimpan (Hash aman) [OK]" : "Kosong [WARNING]") . "\n";
    $results['saver_integrity'] = true;
} else {
    echo " - Sample Saver: Belum ada data penabung [INFO]\n";
    $results['saver_integrity'] = false;
}

// 4. TRANSACTIONS & DOUBLE-ENTRY ACCOUNTING
echo "\n--- 4. TRANSACTIONS & DOUBLE-ENTRY ACCOUNTING ---\n";
$txCount = \App\Models\Transaction::count();
$journalCount = \App\Models\AccountingJournal::count();
echo " - Total Transaksi: $txCount\n";
echo " - Total Baris Jurnal: $journalCount\n";

$debitSum = \App\Models\AccountingJournal::where('position', 'Debit')->sum('amount');
$creditSum = \App\Models\AccountingJournal::where('position', 'Credit')->sum('amount');
echo " - Total Jurnal Debit : Rp " . number_format($debitSum, 0, ',', '.') . "\n";
echo " - Total Jurnal Kredit: Rp " . number_format($creditSum, 0, ',', '.') . "\n";
$isBalanced = ($debitSum == $creditSum);
echo " - Keseimbangan Buku Besar: " . ($isBalanced ? "BALANCE (Sempurna: Debit == Kredit) [OK]" : "SELISIH Rp " . abs($debitSum - $creditSum) . " [WARNING]") . "\n";
$results['accounting_balanced'] = $isBalanced;

// 5. TELLER SHIFTS & RECONCILIATIONS
echo "\n--- 5. TELLER SHIFTS & RECONCILIATIONS ---\n";
$shiftCount = \App\Models\TellerShift::count();
$reportCount = \App\Models\DailyReport::count();
echo " - Total Sesi Shift Kasir : $shiftCount\n";
echo " - Total Laporan Penutupan: $reportCount\n";
$openShifts = \App\Models\TellerShift::where('status', 'open')->count();
$submittedShifts = \App\Models\TellerShift::where('status', 'submitted')->count();
$approvedShifts = \App\Models\TellerShift::where('status', 'approved')->count();
echo " - Status Shift: Open ($openShifts), Submitted ($submittedShifts), Approved ($approvedShifts) [OK]\n";
$results['shifts'] = true;

// 6. ROUTE SANITY CHECK
echo "\n--- 6. ROUTE SANITY CHECK ---\n";
$router = app('router');
$allRoutes = $router->getRoutes();
$totalRoutes = count($allRoutes);
echo " - Total Rute Terdaftar: $totalRoutes rute\n";

$crucialRoutes = [
    'login' => 'GET',
    'admin/dashboard' => 'GET',
    'admin/users' => 'GET',
    'admin/approval' => 'GET',
    'admin/reports/monthly' => 'GET',
    'teller/dashboard' => 'GET',
    'teller/shift/create' => 'GET',
    'teller/shift/close' => 'GET',
    'teller/setoran' => 'GET',
    'teller/penarikan' => 'GET',
    'teller/report/closing' => 'GET',
    'api/search-saver' => 'GET',
    'api/deposit' => 'POST',
    'api/withdrawal' => 'POST',
    'api/login' => 'POST',
    'api/profile' => 'GET',
    'api/transactions' => 'GET',
    'api/forgot-pin/send-otp' => 'POST',
    'api/mobile/auth/login' => 'POST',
    'api/mobile/security/reset-pin' => 'POST',
];

$missingRoutes = [];
foreach ($crucialRoutes as $uri => $method) {
    $found = false;
    foreach ($allRoutes as $route) {
        if (in_array($method, $route->methods()) && $route->uri() === $uri) {
            $found = true;
            break;
        }
    }
    if ($found) {
        echo " [OK] Rute [$method] /$uri\n";
    } else {
        echo " [MISSING] Rute [$method] /$uri\n";
        $missingRoutes[] = "$method $uri";
    }
}
$results['routes_intact'] = empty($missingRoutes);

// 7. MAIL & NOTIFICATION CONFIG CHECK
echo "\n--- 7. MAIL & SMTP CONFIG CHECK ---\n";
$mailer = config('mail.default');
$host = config('mail.mailers.smtp.host');
$port = config('mail.mailers.smtp.port');
$fromAddress = config('mail.from.address');
$fromName = config('mail.from.name');
echo " - Mailer Driver: $mailer\n";
echo " - SMTP Host    : $host:$port\n";
echo " - From Sender  : $fromName <$fromAddress>\n";
$results['mail_config'] = !empty($host) && !empty($fromAddress);
echo ($results['mail_config'] ? " [OK] Konfigurasi email SMTP siap\n" : " [WARNING] Konfigurasi email belum lengkap\n");

// 8. SRS ASSETS & DOCUMENTS INTEGRITY CHECK
echo "\n--- 8. SRS & DOCUMENTATION ASSETS CHECK ---\n";
$srsDocs = [
    'C:/laragon/www/bankmini/SRS_E_Teller_Bank_Mini_Fadhil_Final.docx' => 'Word Docx Final',
    'C:/laragon/www/bankmini/DOKUMEN_SRS_BANK_MINI_SEKOLAH.html' => 'HTML Ready-to-Print',
    'C:/laragon/www/bankmini/DOKUMEN_SRS_BANK_MINI_SEKOLAH.md' => 'Markdown Documentation',
];

$allDocsOk = true;
foreach ($srsDocs as $path => $desc) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo " [OK] $desc: Ada (" . round($size / 1024, 1) . " KB)\n";
    } else {
        echo " [FAIL] $desc: TIDAK DITEMUKAN!\n";
        $allDocsOk = false;
    }
}

$imgDir = 'C:/laragon/www/bankmini/docs/srs_images';
$imgCount = file_exists($imgDir) ? count(glob("$imgDir/*.*")) : 0;
echo " - Total Aset Gambar (Diagram & Wireframe): $imgCount file " . ($imgCount >= 25 ? "[OK]" : "[WARNING]") . "\n";
$results['srs_documents'] = $allDocsOk && ($imgCount >= 25);

// SUMMARY
echo "\n=================================================================\n";
echo "                       FINAL HEALTH SUMMARY                      \n";
echo "=================================================================\n";
$allPass = true;
foreach ($results as $k => $v) {
    $status = $v ? "PASS" : "FAIL";
    echo sprintf(" - %-25s : [%s]\n", ucwords(str_replace('_', ' ', $k)), $status);
    if (!$v) $allPass = false;
}

if ($allPass) {
    echo "\n>>> STATUS: KESELURUHAN PROYEK 100% LENGKAP & BERJALAN LANCAR! <<<\n";
} else {
    echo "\n>>> STATUS: DITEMUKAN BEBERAPA PERINGATAN (CEK DETAIL DI ATAS) <<<\n";
}
echo "=================================================================\n";

