<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ]);

        $credentials = [
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
        ];

        $remember = (bool) ($validated['remember'] ?? false);

        if (! Auth::attempt($credentials, $remember)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.',
            ], 401);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        return response()->json([
            'message' => 'Login successful.',
            'user' => [
                'id'       => $user->id,
                'nickname' => $user->nickname,
                'email'    => $user->email,
                'role'     => $user->role,
            ],
        ]);
    }
}
