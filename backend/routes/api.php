<?php

use App\Http\Controllers\GameController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Admin\AdminApiController;
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
Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
	->name('api.verification.send');
Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])
	->name('api.password.email');
Route::post('/reset-password', [ResetPasswordController::class, 'store'])
	->name('api.password.update');

Route::patch('/profile/nickname', [ProfileController::class, 'updateNickname'])
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
		Authenticate::class,
	])
	->name('api.profile.nickname.update');

Route::patch('/profile/email', [ProfileController::class, 'updateEmail'])
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
		Authenticate::class,
	])
	->name('api.profile.email.update');

Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
		Authenticate::class,
	])
	->name('api.profile.password.update');

Route::post('/profile/password/verify', [ProfileController::class, 'verifyCurrentPassword'])
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
		Authenticate::class,
	])
	->name('api.profile.password.verify');

Route::delete('/profile/account', [ProfileController::class, 'deleteAccount'])
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
		Authenticate::class,
	])
	->name('api.profile.account.delete');

Route::middleware([
	EncryptCookies::class,
	AddQueuedCookiesToResponse::class,
	StartSession::class,
	Authenticate::class,
])->group(function (): void {
	Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
	Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('api.notifications.read');
	Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('api.notifications.read.all');
	Route::get('/wishlist', [WishlistController::class, 'index'])->name('api.wishlist.index');
	Route::get('/wishlist/{game}/status', [WishlistController::class, 'status'])->name('api.wishlist.status');
	Route::post('/wishlist/{game}', [WishlistController::class, 'store'])->name('api.wishlist.store');
	Route::delete('/wishlist/{game}', [WishlistController::class, 'destroy'])->name('api.wishlist.destroy');
});

Route::prefix('/admin')
	->middleware([
		EncryptCookies::class,
		AddQueuedCookiesToResponse::class,
		StartSession::class,
		Authenticate::class,
		'ensure.admin',
	])
	->group(function (): void {
		Route::get('/users', [AdminApiController::class, 'users'])->name('api.admin.users');
		Route::post('/users/{user}/block', [AdminApiController::class, 'blockUser'])->name('api.admin.users.block');
		Route::post('/sync-prices', [AdminApiController::class, 'syncPrices'])->name('api.admin.sync.prices');
		Route::post('/sync-listings', [AdminApiController::class, 'syncListings'])->name('api.admin.sync.listings');
	});

Route::get('/games', [GameController::class, 'index'])->name('api.games.index');
Route::get('/games/{game}', [GameController::class, 'show'])->name('api.games.show');