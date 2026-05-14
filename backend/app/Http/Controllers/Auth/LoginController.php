<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user === null) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'user' => [
                'id'       => $user->id,
                'nickname' => $user->nickname,
                'email'    => $user->email,
                'role'     => $user->role,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

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

        $user = User::query()
            ->where('email', $credentials['email'])
            ->first();

        if ($user !== null && $user->account_status === 'inactive') {
            return response()->json([
                'message' => 'Your account is blocked.',
            ], 403);
        }

        if ($user !== null && ! $user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Please verify your email address before logging in.',
            ], 403);
        }

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
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
