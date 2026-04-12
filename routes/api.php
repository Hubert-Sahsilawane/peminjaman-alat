<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Auth\RegisterController;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\User\UserController;  // ← IMPORT DARI User
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ActivityLogController;
use App\Http\Controllers\Api\Admin\ToolController;
use App\Http\Controllers\Api\Admin\LoanController;
use App\Http\Controllers\Api\Petugas\PetugasController;
use App\Http\Controllers\Api\Peminjam\PeminjamController;

// Login & Register (public)
Route::post('/login', [LoginController::class, 'login']);
Route::post('/register', [RegisterController::class, 'register']);

// Protected routes (perlu token)
Route::middleware('auth:api')->group(function () {
    // Profile (menggunakan UserController)
    Route::get('/profile', [UserController::class, 'profile']);
    Route::put('/profile', [UserController::class, 'updateProfile']);

    // Auth
    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ==================== ADMIN ONLY ====================
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {

        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
        
        // User Management (CRUD by admin)
        Route::apiResource('users', UserController::class);
        Route::get('/users/roles', [UserController::class, 'roles']);

        // Category Management
        Route::apiResource('categories', CategoryController::class);

        // Tool Management
        Route::apiResource('tools', ToolController::class);

        // Loan Management
        Route::apiResource('loans', LoanController::class);
        Route::get('/loans/status/{status}', [LoanController::class, 'byStatus']);
        Route::get('/loans/select/data', [LoanController::class, 'selectData']);
        Route::get('/loans/stats/dashboard', [LoanController::class, 'stats']);
    });

    // ==================== PETUGAS ONLY ====================
    Route::middleware(['role:petugas'])->prefix('petugas')->group(function () {
        Route::get('/dashboard', [PetugasController::class, 'dashboard']);
        Route::post('/approve/{id}', [PetugasController::class, 'approve']);
        Route::post('/reject/{id}', [PetugasController::class, 'reject']);
        Route::post('/return/{id}', [PetugasController::class, 'processReturn']);
        Route::get('/report', [PetugasController::class, 'report']);
        Route::get('/loans/{id}', [PetugasController::class, 'show']);
    });

    // ==================== PEMINJAM ONLY ====================
    Route::middleware(['role:peminjam'])->prefix('peminjam')->group(function () {
        Route::get('/dashboard', [PeminjamController::class, 'dashboard']);
        Route::get('/tools', [PeminjamController::class, 'tools']);
        Route::get('/tools/{id}', [PeminjamController::class, 'toolDetail']);
        Route::get('/cart', [PeminjamController::class, 'getCart']);
        Route::post('/cart', [PeminjamController::class, 'addToCart']);
        Route::put('/cart/{id}', [PeminjamController::class, 'updateCartItem']);
        Route::delete('/cart/{id}', [PeminjamController::class, 'removeFromCart']);
        Route::delete('/cart', [PeminjamController::class, 'clearCart']);
        Route::post('/checkout', [PeminjamController::class, 'checkout']);
        Route::get('/history', [PeminjamController::class, 'history']);
        Route::get('/loans/{id}', [PeminjamController::class, 'loanDetail']);
    });
});
