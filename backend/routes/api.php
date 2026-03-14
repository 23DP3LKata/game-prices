<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Auth\Middleware\Authenticate;
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

Route::patch('/profile/nickname', [ProfileController::class, 'updateNickname'])
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
		Authenticate::class,
	])
	->name('api.profile.nickname.update');

Route::get('/games', [GameController::class, 'index'])->name('api.games.index');
Route::get('/games/{game}', [GameController::class, 'show'])->name('api.games.show');