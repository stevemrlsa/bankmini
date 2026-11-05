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
// MOBILE SECURITY & OTP FLOW (UBAH & LUPA PASSWORD / PIN)
// ---------------------------------------------------------
Route::prefix('mobile/security')->group(function () {
    Route::post('/request-otp', [MobileSecurityApiController::class, 'requestOtp']);
    Route::post('/verify-otp', [MobileSecurityApiController::class, 'verifyOtp']);
    Route::post('/reset-password', [MobileSecurityApiController::class, 'resetPassword']);
    Route::post('/reset-pin', [MobileSecurityApiController::class, 'resetPin']);
});
