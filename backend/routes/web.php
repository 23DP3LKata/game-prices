<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware('signed')
    ->name('verification.verify');

Route::get('/reset-password/{token}', function (Request $request, string $token) {
    $frontendUrl = rtrim((string) config('frontend.url', 'http://localhost:5173'), '/');
    $email = (string) $request->query('email', '');

    return redirect()->away($frontendUrl.'/reset-password?token='.$token.'&email='.urlencode($email));
})->name('password.reset');
