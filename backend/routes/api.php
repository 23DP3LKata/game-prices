<?php

use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisteredUserController::class, 'store'])->name('api.register');
Route::post('/login', [LoginController::class, 'store'])
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
	])
	->name('api.login');