<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    // Daftarkan kolom yang boleh diisi
    protected $fillable = [
        'user_id',
        'food_id',
        'score',
        'review'
    ];
}