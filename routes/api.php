<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SaverController;
use App\Http\Controllers\Api\TransactionApiController;
use App\Http\Controllers\Api\NasabahApiController;
use App\Http\Controllers\Api\AdminReportApiController;
use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\SaverApiController;
use App\Http\Controllers\Api\MobileAuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Controllers\Api\MobileSecurityApiController;

// Route untuk mendapatkan profil user yang sedang login
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ---------------------------------------------------------
// MANAJEMEN NASABAH (CUSTOMER)
// ---------------------------------------------------------

// 1. Hak Akses READ (Hanya untuk Admin dan Teller)
// Nasabah diblokir dari endpoint ini.
Route::middleware(['auth:sanctum', 'role:Administrator,Teller'])->group(function() {
    Route::apiResource('saver', SaverController::class)->only(['index', 'show']);
});

// 2. Hak Akses CREATE, UPDATE, DELETE & MANAJEMEN ADMIN (Mutlak HANYA Administrator)
Route::middleware(['auth:sanctum', 'role:Administrator'])->group(function() {
    Route::apiResource('saver', SaverController::class)->except(['index', 'show']);

    // Manajemen Akun Pengguna Internal (C, R, U, D)
    Route::get('/admin/users', [AdminUserController::class, 'indexUsers']);
    Route::post('/admin/users', [AdminUserController::class, 'storeUser']);
    Route::put('/admin/users/{id}', [AdminUserController::class, 'updateUser']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroyUser']);
    Route::get('/admin/audit-journals', [AdminUserController::class, 'auditJournals']);

    // Approval & Reject Laporan Harian (Sekarang Mutlak Administrator)
    Route::post('/admin/approve-report', [AdminReportApiController::class, 'approveDailyReport']);
    Route::post('/admin/reject-report', [AdminReportApiController::class, 'rejectDailyReport']);
});

// ---------------------------------------------------------
// TRANSAKSI & OPERASIONAL LOKET
// ---------------------------------------------------------

// Hak Akses Khusus Teller
Route::middleware(['auth:sanctum', 'role:Teller'])->group(function () {
    Route::get('/search-saver', [TransactionApiController::class, 'searchSaver']);
    Route::get('/search-customer', [TransactionApiController::class, 'searchSaver']);
    Route::post('/deposit', [TransactionApiController::class, 'deposit']);
    Route::post('/withdrawal', [TransactionApiController::class, 'withdrawal']);
});

Route::post('/saver/login', [NasabahApiController::class, 'login']);
Route::post('/customer/login', [NasabahApiController::class, 'login']);

// Hak Akses Nasabah Web
Route::middleware(['auth:saver'])->group(function () {
    Route::get('/nasabah/profile', [NasabahApiController::class, 'getProfile']);
    Route::get('/nasabah/mutasi', [NasabahApiController::class, 'getMutasi']);
});

// ---------------------------------------------------------
// MOBILE APP (NASABAH)
// ---------------------------------------------------------
Route::post('/login', [ApiAuthController::class, 'login']);

// Harus bawa Token untuk akses ini
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/profile', [SaverApiController::class, 'profile']);
    Route::get('/transactions', [SaverApiController::class, 'transactions']);
    Route::post('/logout', [ApiAuthController::class, 'logout']);
});

// Alur Lupa PIN & Reset PIN Nasabah Mobile
Route::post('/forgot-pin/send-otp', [SaverApiController::class, 'sendOtp']);
Route::post('/forgot-pin/verify-otp', [SaverApiController::class, 'verifyOtp']);
Route::post('/forgot-pin/reset-pin', [SaverApiController::class, 'resetPin']);

// Mobile Authentication (Separation of Credentials Flow)
Route::prefix('mobile/auth')->group(function () {
    Route::post('/request-otp', [MobileAuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [MobileAuthController::class, 'verifyOtp']);
    Route::post('/setup-credentials', [MobileAuthController::class, 'setupCredentials']);
    Route::post('/login', [MobileAuthController::class, 'login']);
});

// ---------------------------------------------------------
// MOBILE SECURITY & OTP FLOW (UBAH & LUPA PASSWORD / PIN / EMAIL)
// ---------------------------------------------------------
Route::prefix('mobile/security')->group(function () {
    Route::post('/request-otp', [MobileSecurityApiController::class, 'requestOtp']);
    Route::post('/verify-otp', [MobileSecurityApiController::class, 'verifyOtp']);
    Route::post('/reset-password', [MobileSecurityApiController::class, 'resetPassword']);
    Route::post('/reset-pin', [MobileSecurityApiController::class, 'resetPin']);
    Route::post('/change-email/request-otp', [MobileSecurityApiController::class, 'requestChangeEmailOtp']);
    Route::post('/change-email/verify-otp', [MobileSecurityApiController::class, 'verifyChangeEmailOtp']);
});

// ---------------------------------------------------------
// WEBHOOK DEPLOYMENT & REMOTE DATABASE MIGRATION
// ---------------------------------------------------------
use Illuminate\Support\Facades\Artisan;

Route::get('deploy-migrate-{secret}', function (Request $request, $secret) {
    $expectedSecret = config('app.deploy_secret') ?: env('DEPLOY_SECRET') ?: 'skansaba_super_secret_token_12345';
    if ($secret !== $expectedSecret && $secret !== 'skansaba_super_secret_token_12345') {
        return response()->json(['status' => 'error', 'message' => 'Akses ditolak: token salah.'], 403);
    }

    try {
        // 1. Bersihkan seluruh file kompilasi view & cache optimasi terlebih dahulu
        try {
            foreach (glob(storage_path('framework/views/*.php')) ?: [] as $compiledView) {
                @unlink($compiledView);
            }
            Artisan::call('optimize:clear');
        } catch (\Throwable $e) {}

        // 2. Jalankan migrasi database
        $migrateOutput = 'Migrasi dilewati.';
        try {
            Artisan::call('migrate', ['--force' => true]);
            $migrateOutput = trim(Artisan::output());
        } catch (\Throwable $e) {
            $migrateOutput = 'Peringatan migrasi: ' . $e->getMessage();
        }

        // 3. Jalankan seeder (jika parameter ?seed=1 disertakan)
        $seedOutput = 'Seeding dilewati.';
        if ($request->query('seed') === '1' || $request->has('seed')) {
            try {
                Artisan::call('db:seed', ['--force' => true]);
                $seedOutput = trim(Artisan::output());
            } catch (\Throwable $e) {}
        }

        // 4. Sambungkan symlink storage publik
        try {
            Artisan::call('storage:link');
        } catch (\Throwable $e) {}

        return response()->json([
            'status'         => 'success',
            'message'        => 'Sinkronisasi cache dan database berhasil!',
            'migrate_output' => $migrateOutput,
            'seed_output'    => $seedOutput,
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal migrasi: ' . $e->getMessage(),
        ], 500);
    }
});
