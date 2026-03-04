<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|alpha_num:ascii|between:3,20|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

         $user = User::create([
        'username' => $request->username,
        'name'     => $request->username, 
        'email'    => $request->email,
        'password' => Hash::make($request->password),  // cast 'hashed' o criptează
    ]);

        return response()->json(['message' => 'User registered successfully!', 'user' => $user], 201);
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Email sau parolă greșite.'], 401);
        }

        // token pentru API
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out'], 200);
    }
}
