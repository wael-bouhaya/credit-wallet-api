<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\AdminWalletController;
use Illuminate\Support\Facades\Route;

// Auth — public
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);

    // Protégées
    Route::middleware('auth:api')->group(function () {
        Route::get('/me',      [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

// Wallet — utilisateur authentifié
Route::middleware('auth:api')->prefix('wallet')->group(function () {
    Route::get('/',       [WalletController::class, 'index']);
    Route::post('/spend', [WalletController::class, 'spend']);
});

// Admin — auth + role admin
Route::middleware(['auth:api', 'role:admin'])->prefix('admin/wallet')->group(function () {
    Route::post('/{user}/credit', [AdminWalletController::class, 'credit']);
    Route::post('/{user}/debit',  [AdminWalletController::class, 'debit']);
});