<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // 1. Fungsi Register
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'kelas' => 'nullable|string',
            'no_hp' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'kelas' => $request->kelas,
            'no_hp' => $request->no_hp,
            'role' => 'siswa', // default saat mendaftar lewat aplikasi
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Register sukses!',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    // 2. Fungsi Login
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login sukses!',
            'data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    // 3. Fungsi Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout, token dihapus.'
        ]);
    }

    /**
     * FUNGSI: Update Profil User
     */
    public function updateProfile(Request $request)
    {
        // 1. Ambil data user yang sedang login saat ini
        $user = $request->user();

        // 2. Validasi input (misal cuma mau ganti nama & email)
        // Kata 'sometimes' berarti: kalau datanya dikirim ya divalidasi, kalau nggak dikirim ya biarin aja
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            // Catatan: Kalau di database kamu nambahin kolom nomor HP (misal 'phone'), 
            // kamu bisa tambahkan di sini juga: 'phone' => 'sometimes|string',
        ]);

        // 3. Update datanya ke database
        $user->update($request->only(['name', 'email'])); // Tambahkan 'phone' di dalam array jika ada

        return response()->json([
            'message' => 'Profil berhasil diperbarui!',
            'data' => $user
        ], 200);
    }
}