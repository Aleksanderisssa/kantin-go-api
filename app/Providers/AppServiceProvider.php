<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Aiven MySQL kadang punya default autocommit=0 di server level.
        // Jika tidak di-override, setiap INSERT tidak ter-commit saat koneksi
        // ditutup → data hilang setelah request selesai.
        // Fix: force SET autocommit=1 setiap kali koneksi MySQL baru dibuat.
        Event::listen(ConnectionEstablished::class, function ($event) {
            try {
                DB::unprepared('SET autocommit = 1');
            } catch (\Exception $e) {
                // Ignore — jangan sampai gagal koneksi hanya karena ini
            }
        });
    }
}
