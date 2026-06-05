<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Masalah: Migration lama membuat kolom status dengan ENUM:
 *   ('pending', 'diproses', 'siap_diambil', 'selesai', 'dibatalkan')
 *
 * Tapi OrderController.php mengisi dengan nilai:
 *   'dipesan', 'dimasak', 'dalam_perjalanan', 'diterima'
 *
 * MySQL strict mode menolak nilai yang tidak ada di ENUM → "Data truncated for column 'status'".
 *
 * Fix: Ubah kolom status menjadi VARCHAR(50) dengan default 'dipesan' agar fleksibel
 * dan konsisten dengan semua nilai yang dipakai di code.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Menggunakan raw SQL karena ALTER ENUM via Blueprint kadang bermasalah
        // di MySQL versi lama. VARCHAR lebih aman dan tidak perlu didefinisikan ulang
        // setiap kali ada status baru.
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status VARCHAR(50) NOT NULL DEFAULT 'dipesan'
        ");

        // Update baris lama yang masih pakai nilai enum lama ke nilai baru
        DB::statement("UPDATE orders SET status = 'dipesan'      WHERE status = 'pending'");
        DB::statement("UPDATE orders SET status = 'dimasak'      WHERE status = 'diproses'");
        DB::statement("UPDATE orders SET status = 'diterima'     WHERE status = 'selesai'");
        DB::statement("UPDATE orders SET status = 'dibatalkan'   WHERE status = 'dibatalkan'");
        DB::statement("UPDATE orders SET status = 'diterima'     WHERE status = 'siap_diambil'");
    }

    public function down(): void
    {
        // Kembalikan ke ENUM lama jika rollback
        DB::statement("
            ALTER TABLE orders
            MODIFY COLUMN status ENUM('pending','diproses','siap_diambil','selesai','dibatalkan')
            NOT NULL DEFAULT 'pending'
        ");
    }
};
