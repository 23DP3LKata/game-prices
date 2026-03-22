<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function updateNickname(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nickname' => ['required', 'string', 'max:100'],
        ]);

        $user = $request->user();
        $user->nickname = trim($validated['nickname']);
        $user->save();

        return response()->json([
            'message' => 'Nickname updated successfully.',
            'user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $normalizedEmail = strtolower(trim($validated['email']));
        $emailChanged = $normalizedEmail !== $user->email;

        $user->email = $normalizedEmail;

        // Email is no longer considered verified after a real change.
        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return response()->json([
            'message' => 'Email updated successfully.',
            'user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        $user->password = $validated['new_password'];
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
            'user' => [
                'id' => $user->id,
                'nickname' => $user->nickname,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    public function verifyCurrentPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'The current password is incorrect.',
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 422);
        }

        return response()->json([
            'message' => 'Current password verified.',
        ]);
    }
}