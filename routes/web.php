<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\WebForgotPasswordController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\AdminEmployeeController;
use App\Http\Controllers\Admin\SaverController;
use App\Http\Controllers\Admin\AdminCustomerController;
use App\Http\Controllers\Admin\AdminMemberController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Teller\TransactionController;
use App\Http\Controllers\Teller\DailyReportController;
use App\Http\Controllers\Teller\TellerActivationController;
use App\Http\Controllers\Admin\ApprovalController as AdminApprovalController;
use App\Http\Controllers\Admin\MonthlyReportController;
use App\Http\Controllers\Admin\JournalController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaverPortalController;
use App\Http\Controllers\TellerShiftController;
use App\Http\Controllers\Admin\AdminShiftController;
use App\Http\Controllers\NotificationController;

// ========================================================
// 1. ROUTE PUBLIK & AUTENTIKASI UTAMA
// ========================================================
Route::get('/', function () {
    if (\Illuminate\Support\Facades\Auth::check()) {
        $role = \Illuminate\Support\Facades\Auth::user()->role;
        if (in_array($role, ['Administrator', 'Supervisor', 'Admin', 'Admin Utama', 'admin_utama'])) {
            return redirect()->route('admin.dashboard');
        } elseif ($role === 'Teller') {
            return redirect()->route('teller.dashboard');
        } elseif ($role === 'Saver') {
            return redirect()->route('saver.dashboard');
        }
    } elseif (\Illuminate\Support\Facades\Session::has('saver_id')) {
        return redirect()->route('saver.dashboard');
    }
    return view('landing');
})->name('landing');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ========================================================
// ALUR LUPA PASSWORD PEGAWAI (OTP VIA EMAIL)
// ========================================================
Route::middleware('web')->group(function () {
    Route::get('/forgot-password', [WebForgotPasswordController::class, 'showEmailForm'])->name('password.request');
    Route::post('/forgot-password', [WebForgotPasswordController::class, 'sendOtp'])->name('password.email');

    Route::get('/forgot-password/otp', [WebForgotPasswordController::class, 'showOtpForm'])->name('password.otp.form');
    Route::post('/forgot-password/otp', [WebForgotPasswordController::class, 'verifyOtp'])->name('password.otp.verify');

    Route::get('/reset-password', [WebForgotPasswordController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('/reset-password', [WebForgotPasswordController::class, 'resetPassword'])->name('password.reset.submit');
});


// ========================================================
// 2A. DATA ANGGOTA (MEMBER DATA) - STANDARD AUTH
// ========================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/member-data', [AdminMemberController::class, 'index'])->name('admin.members.index');
    Route::get('/member-data/import', [AdminMemberController::class, 'importForm'])->name('admin.members.import.form');
    Route::post('/member-data/import', [AdminMemberController::class, 'import'])->name('admin.members.import');
    Route::get('/member-data/template', [AdminMemberController::class, 'downloadTemplate'])->name('admin.members.template');
    Route::post('/member-data/{id}/activate', [AdminMemberController::class, 'activate'])->name('admin.members.activate');

    // Route aliases for backward compatibility and avoiding route mismatches
    Route::get('/admin/member-data', [AdminMemberController::class, 'index'])->name('admin.member-data.index');
    Route::get('/admin/member-data/import', [AdminMemberController::class, 'importForm']);
    Route::post('/admin/member-data/import', [AdminMemberController::class, 'import']);
    Route::get('/admin/member-data/template', [AdminMemberController::class, 'downloadTemplate']);
    Route::post('/admin/member-data/{id}/activate', [AdminMemberController::class, 'activate']);

    Route::get('/admin/savers/import', [AdminMemberController::class, 'importForm'])->name('admin.savers.import.form');
    Route::post('/admin/savers/import', [AdminMemberController::class, 'import'])->name('admin.savers.import');
    Route::get('/admin/savers/template', [AdminMemberController::class, 'downloadTemplate'])->name('admin.savers.template');

    // Member edit, update, delete
    Route::get('/member-data/{id}/edit', [AdminMemberController::class, 'edit'])->name('admin.members.edit');
    Route::put('/member-data/{id}', [AdminMemberController::class, 'update'])->name('admin.members.update');
    Route::delete('/member-data/{id}', [AdminMemberController::class, 'destroy'])->name('admin.members.destroy');
    Route::post('/member-data/{id}/reset-password', [AdminMemberController::class, 'resetPassword'])->name('admin.members.reset-password');
});

// ========================================================
// SISTEM NOTIFIKASI GLOBAL (ADMIN & TELLER)
// ========================================================
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications/fetch', [NotificationController::class, 'fetchNotifications'])->name('notifications.fetch');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-read');

    // Profil Pengguna, Ganti Sandi & Ganti Email via OTP (Batas waktu 2 menit)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::post('/profile/password/otp', [ProfileController::class, 'requestPasswordOtp'])->name('profile.password.otp');
    Route::post('/profile/password/update', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/email/otp', [ProfileController::class, 'requestEmailOtp'])->name('profile.email.otp');
    Route::post('/profile/email/update', [ProfileController::class, 'verifyAndChangeEmail'])->name('profile.email.update');
});

// ========================================================
// 2B. GRUP ADMINISTRATOR (Sistem Utama & Konfigurasi)
// ========================================================
Route::prefix('admin')->middleware(['auth', 'role:Administrator,Admin'])->group(function () {
    
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Manajemen Pegawai / User
    Route::get('/users', [AdminEmployeeController::class, 'index'])->name('admin.users.index');
    Route::get('/employees', [AdminEmployeeController::class, 'index'])->name('admin.employees.index');
    Route::post('/users', [AdminEmployeeController::class, 'store'])->name('admin.users.store');
    Route::put('/users/{id}', [AdminEmployeeController::class, 'update'])->name('admin.users.update');
    Route::delete('/users/{id}', [AdminEmployeeController::class, 'destroy'])->name('admin.users.destroy');

    // Employee Toggle Status & Buka Blokir (Supports PATCH, POST, and standard aliases)
    Route::match(['PATCH', 'POST'], '/users/{id}/toggle-status', [AdminEmployeeController::class, 'toggleStatus'])->name('admin.users.toggle-status');
    Route::match(['PATCH', 'POST'], '/employees/{id}/toggle-status', [AdminEmployeeController::class, 'toggleStatus'])->name('admin.employees.toggle-status');
    Route::match(['PATCH', 'POST'], '/users/{id}/unblock', [AdminEmployeeController::class, 'unblock'])->name('admin.users.unblock');
    Route::match(['PATCH', 'POST'], '/employees/{id}/unblock', [AdminEmployeeController::class, 'unblock'])->name('admin.employees.unblock');
    Route::match(['PATCH', 'POST'], '/employee/{id}/unblock', [AdminEmployeeController::class, 'unblock'])->name('employee.unblock');
    Route::match(['PATCH', 'POST'], '/pegawai/{id}/unblock', [AdminEmployeeController::class, 'unblock'])->name('pegawai.unblock');

    // Master Data Rekening Nasabah (Savers/Customers - Active Students Only)
    Route::get('/savers', [AdminCustomerController::class, 'index'])->name('admin.savers.index');
    Route::post('/savers', [AdminCustomerController::class, 'store'])->name('admin.savers.store');
    Route::get('/customers', [AdminCustomerController::class, 'index'])->name('admin.customers.index');
    Route::post('/customers', [AdminCustomerController::class, 'store'])->name('admin.customers.store');
    Route::post('/customers/bulk-promote', [AdminCustomerController::class, 'bulkPromote'])->name('admin.savers.bulk-promote');
    Route::patch('/customers/{id}/deactivate', [AdminCustomerController::class, 'deactivateAccount'])->name('admin.savers.deactivate');
    Route::get('/customers/{id}/history', [AdminCustomerController::class, 'history'])->name('admin.savers.history');
    Route::put('/savers/{id}', [AdminCustomerController::class, 'update'])->name('admin.savers.update');
    Route::post('/savers/{id}/reset-pin', [AdminCustomerController::class, 'resetPin'])->name('admin.savers.reset-pin');
    Route::post('/savers/{id}/reset-password', [AdminCustomerController::class, 'resetPassword'])->name('admin.savers.reset-password');
    Route::post('/customers/{id}/reset-password', [AdminCustomerController::class, 'resetPassword'])->name('admin.customers.reset-password');
    Route::post('/customer-data/{id}/reset-password', [AdminCustomerController::class, 'resetPassword'])->name('admin.customer-data.reset-password');
    Route::delete('/savers/{id}', [AdminCustomerController::class, 'destroy'])->name('admin.savers.destroy');

    // Approval Laporan Penutupan Kas & Shift Laci Teller (Vault Transfer)
    Route::get('/approval', [AdminApprovalController::class, 'indexApproval'])->name('admin.approval.index');
    Route::get('/approval/kas/{id}/pdf', [AdminApprovalController::class, 'downloadPdf'])->name('admin.approval.pdf');
    Route::post('/approval/kas/{id}', [AdminApprovalController::class, 'approveKas'])->name('admin.approval.approve');
    Route::post('/approval/kas/{id}/reject', [AdminApprovalController::class, 'rejectKas'])->name('admin.approval.reject');

    // Review & Approval Shift Kas Teller (Digabung ke Approval Kas Teller)
    Route::get('/shifts', function () {
        return redirect()->route('admin.approval.index');
    })->name('admin.shifts.index');
    Route::post('/shifts/{id}/approve', [AdminApprovalController::class, 'approveKas'])->name('admin.shifts.approve');

    // Laporan Bulanan (Tabular & PDF)
    Route::get('/reports/monthly', [MonthlyReportController::class, 'index'])->name('admin.reports.monthly');
    Route::get('/reports/monthly/pdf', [MonthlyReportController::class, 'exportPdf'])->name('admin.reports.monthly.pdf');

    // Jurnal Akuntansi (Double-Entry General Ledger)
    Route::get('/journals', [JournalController::class, 'index'])->name('admin.journals.index');
    Route::get('/journals/pdf', [JournalController::class, 'exportPdf'])->name('admin.journals.pdf');

    // Audit Log Sistem (Read-Only)
    Route::get('/audit', [AuditController::class, 'index'])->name('admin.audit.index');

    // Konfigurasi Sistem Global (Admin Prefix)
    Route::get('/settings', [SettingsController::class, 'index'])->name('admin.settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('admin.settings.update');
    Route::post('/settings', [SettingsController::class, 'update'])->name('admin.settings.update.post');
    Route::get('/system-settings', [SettingsController::class, 'index']);
    Route::put('/system-settings', [SettingsController::class, 'update']);
});

// Root-level Aliases for Settings & Employee Actions (Outside /admin prefix)
Route::middleware(['auth', 'role:Administrator,Admin'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings', [SettingsController::class, 'update']);
    Route::match(['PATCH', 'POST'], '/employee/{id}/unblock', [AdminEmployeeController::class, 'unblock']);
    Route::match(['PATCH', 'POST'], '/pegawai/{id}/unblock', [AdminEmployeeController::class, 'unblock']);
});


// ========================================================
// 3. GRUP TELLER (Front-End Transaction)
// ========================================================
Route::prefix('teller')->middleware(['auth', 'role:Teller'])->group(function () {
    
    // Manajemen Shift / Buka Laci Kasir (TIDAK boleh dilindungi teller.shift untuk cegah loop redirect)
    Route::get('/shift/create', [TellerShiftController::class, 'create'])->name('teller.shift.create');
    Route::post('/shift', [TellerShiftController::class, 'store'])->name('teller.shift.store');

    // Rute Operasional & Transaksi (Wajib memiliki Shift / Modal Awal yang Aktif)
    Route::middleware('teller.shift')->group(function () {
        // Penutupan Shift Kasir
        Route::get('/shift/close', [TellerShiftController::class, 'close'])->name('teller.shift.close');
        Route::post('/shift/end', [TellerShiftController::class, 'endShift'])->name('teller.shift.end');

        // Dashboard Operasional & Indeks Transaksi Teller
        Route::get('/dashboard', [TransactionController::class, 'dashboard'])->name('teller.dashboard');
        Route::get('/transactions', [TransactionController::class, 'dashboard'])->name('teller.transactions.index');
        
        // Pencarian Data Nasabah (QR Scan & Auto-Fill AJAX)
        Route::get('/search-saver', [TransactionController::class, 'searchSaver'])->name('teller.search-saver');
        Route::get('/search-customer', [TransactionController::class, 'searchSaver'])->name('teller.search-customer');

        // Setoran (Deposit)
        Route::get('/setoran', [TransactionController::class, 'createSetoran'])->name('teller.setoran.create');
        Route::get('/deposit', [TransactionController::class, 'createSetoran'])->name('teller.transaction.deposit');
        Route::post('/setoran', [TransactionController::class, 'storeSetoran'])->name('teller.setoran.store');
        Route::post('/deposit', [TransactionController::class, 'storeSetoran'])->name('teller.deposit');
        
        // Penarikan & Reset PIN Nasabah Lupa PIN (Withdrawal)
        Route::get('/penarikan', [TransactionController::class, 'createPenarikan'])->name('teller.penarikan.create');
        Route::get('/withdrawal', [TransactionController::class, 'createPenarikan'])->name('teller.transaction.withdrawal');
        Route::post('/penarikan', [TransactionController::class, 'storePenarikan'])->name('teller.penarikan.store');
        Route::post('/withdrawal', [TransactionController::class, 'storePenarikan'])->name('teller.withdrawal');
        Route::post('/savers/reset-pin', [TransactionController::class, 'resetPin'])->name('teller.savers.reset-pin');
        
        // Laporan Penutupan Kas Harian
        Route::get('/report/closing', [DailyReportController::class, 'index'])->name('teller.report.closing');
        Route::post('/report/closing', [DailyReportController::class, 'store'])->name('teller.report.store');

        // One-Stop Aktivasi Nasabah Baru
        Route::get('/activations', [TellerActivationController::class, 'index'])->name('teller.activations.index');
        Route::post('/activations/{id}', [TellerActivationController::class, 'activate'])->name('teller.activations.activate');
    });

});

// Global alias for /search-saver, /deposit, /withdrawal accessible with teller auth and active shift
Route::middleware(['auth', 'role:Teller', 'teller.shift'])->group(function () {
    Route::get('/search-saver', [TransactionController::class, 'searchSaver']);
    Route::get('/search-customer', [TransactionController::class, 'searchSaver']);
    Route::post('/deposit', [TransactionController::class, 'storeSetoran']);
    Route::post('/withdrawal', [TransactionController::class, 'storePenarikan']);
});


// ========================================================
// 5. PORTAL NASABAH (Siswa End-User)
// ========================================================
Route::get('/nasabah/login', [SaverPortalController::class, 'showLoginForm'])->name('saver.login');
Route::post('/nasabah/login', [SaverPortalController::class, 'login'])->name('saver.login.submit');
Route::post('/nasabah/logout', [SaverPortalController::class, 'logout'])->name('saver.logout');

// Dilindungi middleware kustom session saver_id
Route::middleware(['saver.auth'])->group(function () {
    Route::get('/nasabah/dashboard', [SaverPortalController::class, 'dashboard'])->name('saver.dashboard');
    Route::get('/nasabah/statement', [SaverPortalController::class, 'statement'])->name('saver.statement');
    Route::get('/nasabah/profile', [SaverPortalController::class, 'profile'])->name('saver.profile');
    
    // Ganti Kata Sandi Nasabah via OTP
    Route::post('/nasabah/profile/password/otp', [SaverPortalController::class, 'requestPasswordOtp'])->name('saver.profile.password.otp');
    Route::post('/nasabah/profile/password/update', [SaverPortalController::class, 'updatePasswordWithOtp'])->name('saver.profile.password.update');
    
    // Ganti PIN 6-Digit Nasabah via OTP
    Route::post('/nasabah/profile/pin/otp', [SaverPortalController::class, 'requestPinOtp'])->name('saver.profile.pin.otp');
    Route::post('/nasabah/profile/pin/update', [SaverPortalController::class, 'updatePinWithOtp'])->name('saver.profile.pin.update');
    
    // Ganti Email Nasabah via OTP
    Route::post('/nasabah/profile/email/otp', [SaverPortalController::class, 'requestEmailOtp'])->name('saver.profile.email.otp');
    Route::post('/nasabah/profile/email/update', [SaverPortalController::class, 'verifyAndChangeEmail'])->name('saver.profile.email.update');

    Route::post('/nasabah/security', [SaverPortalController::class, 'updateSecurity'])->name('saver.security.update');
    Route::post('/nasabah/savings-goal', [SaverPortalController::class, 'updateSavingsGoal'])->name('saver.goal.update');
});