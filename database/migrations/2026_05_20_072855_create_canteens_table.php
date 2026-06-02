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
    Schema::create('canteens', function (Blueprint $table) {
        $table->id();
        $table->string('name'); // Nama Stan Kantin
        $table->string('owner_name'); // Nama Penjual
        $table->string('no_stan')->nullable(); // Nomor Lapak
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canteens');
    }
};
