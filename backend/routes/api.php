<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\TiketController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\TagihanMemberController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ExitController;
use App\Http\Controllers\ForgotPasswordController;

// =========================
// Public Routes
// =========================

Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password/check-email', [ForgotPasswordController::class, 'checkEmail']);
Route::post('/forgot-password/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [ForgotPasswordController::class, 'resetPassword']);

// Health check endpoint
Route::get('/health', fn () => response()->json(['status' => 'ok']));

// Kiosk / User: ambil tiket tanpa login
Route::post('/tiket', [TiketController::class, 'create']);


// =========================
// Protected Routes
// =========================

Route::middleware('auth:sanctum')->group(function () {

    // =========================
    // Auth
    // =========================
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    // =========================
    // Tiket
    // =========================
    Route::get('/tiket', [TiketController::class, 'index']);
    Route::get('/tiket/{qr_code}/cek', [TiketController::class, 'cek']);
    Route::post('/tiket/masuk', [TiketController::class, 'masuk']);

    // =========================
    // Transaksi Gerbang
    // =========================
    Route::post('/scan', [ScanController::class, 'scan']);

    Route::prefix('keluar')->group(function () {
        Route::post('/scan', [ExitController::class, 'scan']);
        Route::post('/member', [ExitController::class, 'member']);
        Route::post('/bayar', [ExitController::class, 'bayar']);
    });

    // =========================
    // Member
    // =========================
    Route::put('/members/{id}/lunas', [MemberController::class, 'lunas']);
    Route::apiResource('members', MemberController::class);

    // =========================
    // Tagihan Member
    // =========================
    Route::get('/tagihan', [TagihanMemberController::class, 'index']);
    Route::get('/tagihan/{tagihanMember}', [TagihanMemberController::class, 'show']);
    Route::post('/tagihan/{tagihanMember}/bayar', [TagihanMemberController::class, 'bayar']);

    // =========================
    // Admin / Super Admin
    // =========================
    Route::middleware('role:admin,super_admin')
        ->prefix('users')
        ->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::post('/', [UserController::class, 'store']);
            Route::get('/{id}', [UserController::class, 'show']);
            Route::put('/{id}', [UserController::class, 'update']);
            Route::delete('/{id}', [UserController::class, 'destroy']);
        });

    // =========================
    // Rekap / Laporan
    // Super Admin + Petugas
    // =========================
    Route::middleware('role:admin,super_admin,petugas')
        ->prefix('rekap')
        ->group(function () {
            Route::get('/summary', [RekapController::class, 'summary']);
            Route::get('/harian', [RekapController::class, 'harian']);
            Route::get('/mingguan', [RekapController::class, 'mingguan']);
            Route::get('/bulanan', [RekapController::class, 'bulanan']);
        });
});
