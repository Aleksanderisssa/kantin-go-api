<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    // Sesuai di phpMyAdmin/HeidiSQL
    protected $table = 'order_details';

    protected $fillable = [
        'order_id', 
        'food_id', 
        'quantity', 
        'price' 
    ];

    // Relasi balik: Detail ini milik pesanan (Order) yang mana
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    // Relasi: Makanan apa yang dipesan
    public function food() 
    {
        return $this->belongsTo(Food::class, 'food_id');
    }
}