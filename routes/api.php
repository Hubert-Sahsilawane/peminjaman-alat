<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ToolController;
use App\Http\Controllers\Api\Admin\LoanController;
use App\Http\Controllers\Api\Petugas\PetugasController;
use App\Http\Controllers\Api\Peminjam\PeminjamController;

// Login (public)
Route::post('/login', [LoginController::class, 'login']);

// Protected routes (perlu token)
Route::middleware('auth:api')->group(function () {

    Route::post('/logout', [LogoutController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Admin only routes
    Route::middleware(['role:admin'])->prefix('admin')->group(function () {
        // User Management
        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{id}', [UserController::class, 'show']);
        Route::put('/users/{id}', [UserController::class, 'update']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);


        //Category Management
        Route::get('/categories', [CategoryController::class, 'index']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::get('/categories/{id}', [CategoryController::class, 'show']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);


        //Tool Management
        Route::get('/tools', [ToolController::class, 'index']);
        Route::post('/tools', [ToolController::class, 'store']);
        Route::get('/tools/{id}', [ToolController::class, 'show']);
        Route::put('/tools/{id}', [ToolController::class, 'update']);
        Route::delete('/tools/{id}', [ToolController::class, 'destroy']);


        //Loan Management (nanti ditambah)
        Route::get('/loans', [LoanController::class, 'index']);
        Route::post('/loans', [LoanController::class, 'store']);
        Route::get('/loans/{id}', [LoanController::class, 'show']);
        Route::put('/loans/{id}', [LoanController::class, 'update']);
        Route::delete('/loans/{id}', [LoanController::class, 'destroy']);
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
        Route::post('/submit', [PeminjamController::class, 'submitLoan']);
        Route::get('/history', [PeminjamController::class, 'history']);
        Route::get('/loans/{id}', [PeminjamController::class, 'loanDetail']);
    });
});
