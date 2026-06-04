<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Order;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function checkout(Request $request)
    {
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

            foreach ($request->items as $item) {
                $food = DB::table('foods')->where('id', $item['food_id'])->first();
                $subTotal = $food->price * $item['quantity'];
                $totalPrice += $subTotal;
                $orderItems[] = [
                    'food_id' => $food->id,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subTotal
                ];
            }

            $userId = $request->user()->id;

            $order = Order::create([
                'user_id' => $userId,
                'total_price' => $totalPrice,
                'status' => 'dipesan',
            ]);

            foreach ($orderItems as $orderItem) {
                DB::table('order_details')->insert([
                    'order_id' => $order->id,
                    'food_id' => $orderItem['food_id'],
                    'quantity' => $orderItem['quantity'],
                    'price' => $orderItem['subtotal'],
                    'created_at' => now()->toDateTimeString(),
                    'updated_at' => now()->toDateTimeString()
                ]);
            }

            // Midtrans config — di dalam fungsi
            Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            Config::$isSanitized = true;
            Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $order->id,
                    'gross_amount' => $order->total_price,
                ],
                'customer_details' => [
                    'first_name' => $request->user()->name,
                    'email' => $request->user()->email,
                ],
            ];

            $snapToken = Snap::getSnapToken($params);

            DB::commit();

            return response()->json([
                'message' => 'Pesanan berhasil dibuat!',
                'order_id' => $order->id,
                'total_bayar' => $totalPrice,
                'snap_token' => $snapToken,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Transaksi gagal.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:dipesan,dimasak,dalam_perjalanan,diterima'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan!'], 404);
        }

        $order->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Status pesanan berhasil diperbarui!',
            'data' => $order
        ], 200);
    }

    public function history(Request $request)
    {
        $userId = $request->user() ? $request->user()->id : null;

        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

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