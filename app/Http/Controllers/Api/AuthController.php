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
            'message'      => 'Register sukses!',
            'data'         => $user,
            'access_token' => $token,
            'token_type'   => 'Bearer',
            // DEBUG TEMPORARY
            '_debug' => [
                'db_host'    => config('database.connections.mysql.host'),
                'db_name'    => config('database.connections.mysql.database'),
                'user_count' => \App\Models\User::count(),
                'autocommit' => \Illuminate\Support\Facades\DB::select('SELECT @@autocommit as ac')[0]->ac ?? 'unknown',
                'server'     => gethostname(),
                'pid'        => getmypid(),
            ],
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
            // Raw SQL langsung ke MySQL, bypass Eloquent & query builder cache
            $allUsers  = \Illuminate\Support\Facades\DB::select('SELECT id, email FROM users ORDER BY id');
            $directHit = \Illuminate\Support\Facades\DB::select(
                'SELECT id, email FROM users WHERE email = ?', [$request->email]
            );

            return response()->json([
                'message' => 'Email atau password salah.',
                '_debug'  => [
                    'user_found'    => $user ? true : false,
                    'db_host'       => config('database.connections.mysql.host'),
                    'db_name'       => config('database.connections.mysql.database'),
                    'user_count_eloquent' => \App\Models\User::count(),
                    'user_count_raw'      => \Illuminate\Support\Facades\DB::select('SELECT COUNT(*) as c FROM users')[0]->c,
                    'all_users_raw' => $allUsers,
                    'direct_hit'    => $directHit,
                    'server'        => gethostname(),
                    'pid'           => getmypid(),
                ],
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

    // 4. Get Profile (wrapped response untuk Android)
    public function getProfile(Request $request)
    {
        return response()->json([
            'message' => 'Berhasil mengambil profil.',
            'data' => $request->user(),
        ], 200);
    }

    // 5. Update Profil
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name'  => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only(['name', 'email']));

        return response()->json([
            'message' => 'Profil berhasil diperbarui!',
            'data'    => $user,
        ], 200);
    }

    // 6. Ganti Password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password'          => 'required',
            'new_password'              => 'required|string|min:6|confirmed',
            'new_password_confirmation' => 'required',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Password lama tidak sesuai.',
            ], 422);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json([
            'message' => 'Password berhasil diubah!',
        ], 200);
    }
}