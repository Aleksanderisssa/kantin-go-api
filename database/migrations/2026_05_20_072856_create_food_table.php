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
    Schema::create('foods', function (Blueprint $table) {
        $table->id();
        $table->foreignId('canteen_id')->constrained('canteens')->onDelete('cascade');
        $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
        $table->string('name');
        $table->integer('price');
        $table->string('image')->nullable();
        $table->boolean('is_available')->default(true); // Stok ready atau habis
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food');
    }
};
