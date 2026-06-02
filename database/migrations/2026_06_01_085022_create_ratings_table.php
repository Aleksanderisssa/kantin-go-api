<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('ratings', function (Blueprint $table) {
        $table->id();
        // user_id diambil dari siswa yang login
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        // food_id adalah makanan yang dinilai
        $table->foreignId('food_id')->constrained('foods')->onDelete('cascade'); 
        // score untuk nilai bintang (misal 1 sampai 5)
        $table->integer('score'); 
        // Ulasan teks opsional
        $table->text('review')->nullable(); 
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('ratings');
    }
};
