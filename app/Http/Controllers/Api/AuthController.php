<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated());

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ], 201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        // Выполняем проверку хеша даже для неизвестного email
        // для уменьшения различий во времени ответа.
        $validPassword = Hash::check(
            $request->password,
            $user?->password ?? Hash::make('invalid-password')
        );

        if (!$user || !$validPassword) {
            return response()->json([
                'message' => 'Неверный email или пароль',
            ], 401);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'data' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function logoutAll(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->noContent();
    }
}
