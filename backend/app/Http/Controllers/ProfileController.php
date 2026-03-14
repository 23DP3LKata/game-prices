<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}