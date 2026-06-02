<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Food;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    // Tampilkan semua makanan yang tersedia
    public function index(Request $request)
    {
        // 1. Mulai merangkai query ke tabel foods
        $query = \App\Models\Food::query();

        // 2. Cek apakah ada request filter 'canteen_id'
        if ($request->has('canteen_id')) {
            $query->where('canteen_id', $request->canteen_id);
        }

        // 3. Cek apakah ada request filter 'category_id'
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 4. (Bonus) Cek apakah ada request pencarian nama 'search'
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 5. Eksekusi query dan ambil datanya
        $foods = $query->get();

        return response()->json([
            'message' => 'Berhasil mengambil daftar makanan',
            'data' => $foods
        ], 200);
    }

    // Tambah menu makanan baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'canteen_id' => 'required|exists:canteens,id',
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'price' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $food = Food::create([
            'canteen_id' => $request->canteen_id,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description, // WAJIB ADA
            'add_ons' => $request->add_ons,         // WAJIB ADA
            'is_available' => true
        ]);

        return response()->json([
            'message' => 'Menu makanan berhasil ditambahkan!',
            'data' => $food
        ], 201);
    }
}