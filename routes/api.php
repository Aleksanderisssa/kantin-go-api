<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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
// DEBUG CONNECTION — Hanya untuk troubleshooting. Hapus setelah production.
// Akses: GET /api/debug-connection
// =========================================================================
Route::get('/debug-connection', function () {
    $result = [
        'php_version'    => PHP_VERSION,
        'laravel_version' => app()->version(),
        'db_driver'      => config('database.default'),
        'db_host'        => config('database.connections.mysql.host'),
        'db_port'        => config('database.connections.mysql.port'),
        'db_name'        => config('database.connections.mysql.database'),
        'database_url_set' => !empty(env('DATABASE_URL')),
    ];

    try {
        DB::connection()->getPdo();
        $result['db_connected']  = true;
        $result['user_count']    = \App\Models\User::count();
        $result['token_count']   = DB::table('personal_access_tokens')->count();
        $result['order_count']   = DB::table('orders')->count();

        // Cek apakah kolom status orders sudah VARCHAR (bukan ENUM lama)
        $col = DB::select("SHOW COLUMNS FROM orders LIKE 'status'");
        $result['orders_status_type'] = $col[0]->Type ?? 'unknown';

    } catch (\Exception $e) {
        $result['db_connected'] = false;
        $result['db_error']     = $e->getMessage();
    }

    return response()->json($result);
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
    Route::get('/user-profile', [AuthController::class, 'getProfile']);

    // Android memanggil /update-profile, bukan /user-profile
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);

    Route::post('/change-password', [AuthController::class, 'changePassword']);

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