<?php

use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\ForgotPasswordOtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordUpdateController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
});

// Email verification is handled via the OTP endpoints; legacy signed-link route removed.

Route::post('email/otp/verify', [EmailOtpController::class, 'verify']);
Route::post('email/otp/resend', [EmailOtpController::class, 'resend']);
Route::post('password/otp/send', [ForgotPasswordOtpController::class, 'send']);
Route::post('password/otp/verify', [ForgotPasswordOtpController::class, 'verify']);
Route::post('password/reset', [ForgotPasswordOtpController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');
    Route::post('token/refresh', RefreshTokenController::class)->name('token.refresh');

    Route::get('me', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('user', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('verified')->group(function () {
        Route::delete('user', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::patch('user/password', PasswordUpdateController::class)->name('password.update');
    });

    // Email verification notifications are now sent using the OTP flow; legacy route removed.


    require __DIR__ . '/admin.php';
    require __DIR__ . '/user.php';

    // (email OTP endpoints are public and already registered)
});
