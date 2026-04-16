<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, int $id, string $hash): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return $this->redirectToFrontend('invalid');
        }

        $user = User::query()->find($id);

        if (! $user || ! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return $this->redirectToFrontend('invalid');
        }

        if ($user->hasVerifiedEmail()) {
            return $this->redirectToFrontend('already-verified');
        }

        $user->markEmailAsVerified();
        event(new Verified($user));

        return $this->redirectToFrontend('verified');
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::query()
            ->where('email', strtolower(trim($validated['email'])))
            ->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'If your account exists and is not verified, a verification email has been sent.',
        ]);
    }

    private function redirectToFrontend(string $status): RedirectResponse
    {
        $frontendUrl = rtrim((string) config('frontend.url', 'http://localhost:5173'), '/');

        return redirect()->away($frontendUrl.'/login?verify='.urlencode($status));
    }
}
