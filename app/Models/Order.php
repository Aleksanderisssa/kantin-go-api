<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Pastikan Laravel tahu ini menggunakan tabel 'orders'
    protected $table = 'orders';

    // Kolom yang diizinkan untuk diisi, TERMASUK status
    protected $fillable = [
        'user_id', 
        'total_price', 
        'status' 
    ];

    // Relasi: Satu pesanan (Order) memiliki banyak detail pesanan (OrderDetail)
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    // Relasi: Pesanan ini milik User siapa
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}