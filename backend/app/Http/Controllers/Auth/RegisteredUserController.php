<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nickname' => ['required', 'string', 'min:4', 'max:100', 'regex:/^[A-Za-z0-9]+$/', 'unique:users,nickname'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*\\d)(?=.*[^A-Za-z0-9]).+$/',
            ],
        ], [
            'nickname.min' => 'Usernames must be between 4 and 100 characters.',
            'nickname.max' => 'Usernames must be between 4 and 100 characters.',
            'nickname.regex' => 'Usernames must only contain alphanumeric characters.',
            'nickname.unique' => 'This username is unavailable.',
            'email.email' => 'Please enter a valid email.',
            'password.min' => 'Use at least 8 characters, uppercase letter, number, special character.',
            'password.regex' => 'Use at least 8 characters, uppercase letter, number, special character.',
        ]);

        $user = User::create([
            'nickname' => trim($validated['nickname']),
            'email' => strtolower(trim($validated['email'])),
            'password' => $validated['password'],
            'role' => 'user',
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Registration completed successfully. Please verify your email.',
            'user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified_at' => $user->email_verified_at,
            ],
        ], 201);
    }

    public function checkNickname(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nickname' => ['required', 'string', 'min:4', 'max:100', 'regex:/^[A-Za-z0-9]+$/'],
        ]);

        $exists = User::where('nickname', $validated['nickname'])->exists();

        return response()->json([
            'available' => !$exists,
        ]);
    }
}