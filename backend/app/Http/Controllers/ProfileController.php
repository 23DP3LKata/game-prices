<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
}