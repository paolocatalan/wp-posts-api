<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function store(StoreUserRequest $request): JsonResponse {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'user' => $user,
            'token' => $user->createToken($request->ip())->plainTextToken
        ]);
    }

    public function login(Request $request): JsonResponse {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string']
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json([
                'message' => 'Credentials does not match'
            ], 401);
        }

        $user = User::where('email', $request->email)->first();

        return response()->json([
            'user' => $user,
            'token' => $user->createToken($request->ip())->plainTextToken
        ]);
    }

    public function logout(): JsonResponse {
        Auth::user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'You have been successfully logged out and your token has been deleted.'
        ], 200);
    }
}
