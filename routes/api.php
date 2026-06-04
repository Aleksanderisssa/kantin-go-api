<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CanteenController;
use App\Http\Controllers\Api\FoodController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RatingController; // <-- TAMBAHAN 1: Import RatingController

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =========================================================================
// HEALTH CHECK — untuk verifikasi Railway deployment
// =========================================================================
Route::get('/health', function () {
    return response()->json([
        'status'  => 'ok',
        'app'     => config('app.name'),
        'env'     => config('app.env'),
        'php'     => PHP_VERSION,
    ]);
});

// =========================================================================
// PUBLIC ROUTES (Bisa diakses langsung tanpa login/token)
// =========================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route melihat daftar kantin & menu makanan (untuk konsumsi aplikasi siswa)
Route::get('/canteens', [CanteenController::class, 'index']);
Route::get('/foods', [FoodController::class, 'index']);


// =========================================================================
// PROTECTED ROUTES (Wajib Login & membawa Bearer Token Sanctum)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
   // Autentikasi & Profil Siswa
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user-profile', function (Request $request) {
        return $request->user();
    });
    
    // INI ROUTE BARU UNTUK UPDATE PROFIL
    Route::put('/user-profile', [AuthController::class, 'updateProfile']);

    // Manajemen Kantin & Makanan (Sisi Penjual/Admin)
    Route::post('/canteens', [CanteenController::class, 'store']);
    Route::post('/foods', [FoodController::class, 'store']);

    // ROUTE BARU: Update Status Pesanan (dipesan -> dimasak -> dalam_perjalanan -> diterima)
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);

    // Transaksi Pemesanan Makanan (Sisi Siswa)
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders/history', [OrderController::class, 'history']);
    
    // Rating Makanan — dua path agar kompatibel dengan Android (/ratings) dan typo lama (/rating)
    Route::post('/ratings', [RatingController::class, 'store']);
    Route::post('/rating',  [RatingController::class, 'store']); // backward compat
    
});