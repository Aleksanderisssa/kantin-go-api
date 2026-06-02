<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Canteen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CanteenController extends Controller
{
    // Tampilkan semua stan kantin
    public function index()
    {
        $canteens = Canteen::all();
        return response()->json([
            'message' => 'Daftar semua kantin',
            'data' => $canteens
        ], 200);
    }

    // Tambah stan kantin baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'owner_name' => 'required|string|max:255',
            'no_stan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $canteen = Canteen::create($request->all());

        return response()->json([
            'message' => 'Kantin berhasil ditambahkan!',
            'data' => $canteen
        ], 201);
    }
}