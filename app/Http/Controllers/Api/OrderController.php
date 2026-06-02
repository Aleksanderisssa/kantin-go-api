<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;

class OrderController extends Controller
{
    /**
     * FUNGSI 1: Membuat Pesanan Baru (Checkout)
     */
    public function checkout(Request $request)
    {
        // 1. Validasi Input
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.food_id' => 'required|exists:foods,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            $totalPrice = 0;
            $orderItems = [];

            // 2. Hitung harga dan validasi stok/harga
            foreach ($request->items as $item) {
                $food = DB::table('foods')->where('id', $item['food_id'])->first();
                
                $subTotal = $food->price * $item['quantity'];
                $totalPrice += $subTotal;

                $orderItems[] = [
                    'food_id' => $food->id,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subTotal // Kita simpan sementara di array ini
                ];
            }

            // 3. Simpan ke tabel 'orders'
            // Pastikan user sedang login
            $userId = $request->user() ? $request->user()->id : null;
            
            $orderId = DB::table('orders')->insertGetId([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'status' => 'dipesan', // Status awal saat checkout
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 4. Simpan ke tabel 'order_details'
            foreach ($orderItems as $orderItem) {
                DB::table('order_details')->insert([
                    'order_id' => $orderId,
                    'food_id' => $orderItem['food_id'],
                    'quantity' => $orderItem['quantity'],
                    'price' => $orderItem['subtotal'], // Disesuaikan dengan nama kolom di database kamu (price)
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat!',
                'order_id' => $orderId,
                'total_bayar' => $totalPrice
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Return pesan eror asli agar kita tahu bagian mana yang gagal
            return response()->json([
                'message' => 'Transaksi gagal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * FUNGSI 2: Update Status Pengiriman Pesanan
     */
    public function updateStatus(Request $request, $id)
    {
        // 1. Validasi agar status yang dikirim hanya 4 pilihan ini
        $request->validate([
            'status' => 'required|in:dipesan,dimasak,dalam_perjalanan,diterima'
        ]);

        // 2. Cari data pesanan berdasarkan ID
        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'message' => 'Pesanan tidak ditemukan!'
            ], 404);
        }

        // 3. Update statusnya
        $order->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui!',
            'data' => $order
        ], 200);
    }

    /**
     * FUNGSI 3: Melihat Riwayat Pesanan (Order History)
     */
    public function history(Request $request)
    {
        // Pastikan user sedang login
        $userId = $request->user() ? $request->user()->id : null;

        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Ambil data pesanan milik user ini, urutkan dari yang paling baru
        // Kita gunakan 'with' untuk menarik data detail pesanan dan nama makanannya sekaligus
        $orders = Order::with(['orderDetails.food'])
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'message' => 'Berhasil mengambil riwayat pesanan!',
            'data' => $orders
        ], 200);
    }
}