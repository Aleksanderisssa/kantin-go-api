<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;

class RatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'food_id' => 'required|exists:foods,id',
            'score'   => 'required|integer|min:1|max:5', 
            'review'  => 'nullable|string'
        ]);

        $rating = Rating::create([
            'user_id' => auth()->id(), 
            'food_id' => $request->food_id,
            'score'   => $request->score,
            'review'  => $request->review,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih, rating berhasil ditambahkan!',
            'data'    => $rating
        ], 201);
    }
}