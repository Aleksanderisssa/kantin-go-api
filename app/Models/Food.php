<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Food extends Model
{
    use HasFactory;

    // BARIS INI WAJIB ADA AGAR LARAVEL TIDAK NYASAR MENCARI TABEL 'food'
    protected $table = 'foods';

    // 1. Tambahkan description dan add_ons ke sini biar diizinkan masuk
    protected $fillable = [
        'canteen_id', 
        'category_id', 
        'name', 
        'price', 
        'image', 
        'is_available',
        'description', // Tambahan baru
        'add_ons'      // Tambahan baru
    ];

    // 2. Tambahkan ini biar Postman bisa ngirim Add-ons pakai format Array/Kurung Siku [ ]
    protected $casts = [
        'add_ons' => 'array',
    ];
}