<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Menambah kolom ke tabel users
        Schema::table('users', function (Blueprint $table) {
            $table->string('photo')->nullable();
            $table->string('class')->nullable();
        });

        // Menambah kolom ke tabel foods
        Schema::table('foods', function (Blueprint $table) {
            $table->text('description')->nullable();
            $table->text('add_ons')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Menghapus kolom jika ingin dibatalkan
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['photo', 'class']);
        });

        Schema::table('foods', function (Blueprint $table) {
            $table->dropColumn(['description', 'add_ons']);
        });
    }
};