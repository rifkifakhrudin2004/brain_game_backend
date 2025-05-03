<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use App\Models\User;
    use Illuminate\Support\Facades\Hash;
    use Illuminate\Support\Facades\Validator;

    class AuthController extends Controller
    {
        // Fungsi untuk registrasi
        public function register(Request $request)
        {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'username' => 'required|string|max:100|unique:users',
                'email' => 'required|string|email|max:100|unique:users',
                'password' => 'required|string|min:6',
                'role' => 'in:user' // hanya izinkan mendaftar sebagai user
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Buat user baru
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'user', // defaultkan ke 'user' agar tidak bisa daftar sebagai admin
            ]);

            // Buat token untuk user
            $token = $user->createToken('GameApp')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        }

        // Fungsi untuk login
        public function login(Request $request)
        {
            // Validasi input
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Cek apakah user ada
            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Email atau password salah'], 401);
            }

            // Batasi akses login hanya untuk user dengan role 'user'
            if ($user->role !== 'user') {
                return response()->json(['message' => 'Akses ditolak, hanya pengguna dengan role user yang dapat login'], 403);
            }

            // Buat token untuk user
            $token = $user->createToken('GameApp')->plainTextToken;

            return response()->json([
                'user' => $user,
                'token' => $token,
            ]);
        }
    }
