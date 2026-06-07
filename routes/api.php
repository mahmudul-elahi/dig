<?php

use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordUpdateController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SendVerificationNotificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:5,1')->group(function () {
    Route::post('register', RegisterController::class)->name('register');
    Route::post('login', LoginController::class)->name('login');
});

Route::get('email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware('signed')
    ->name('verification.verify');

Route::post('email/otp/verify', [EmailOtpController::class, 'verify']);
Route::post('email/otp/resend', [EmailOtpController::class, 'resend']);
Route::post('password/otp/send', [\App\Http\Controllers\Auth\ForgotPasswordOtpController::class, 'send']);
Route::post('password/otp/verify', [\App\Http\Controllers\Auth\ForgotPasswordOtpController::class, 'verify']);
Route::post('password/reset', [\App\Http\Controllers\Auth\ForgotPasswordOtpController::class, 'reset']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', LogoutController::class)->name('logout');
    Route::post('token/refresh', RefreshTokenController::class)->name('token.refresh');

    Route::get('me', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('user', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('verified')->group(function () {
        Route::delete('user', [ProfileController::class, 'destroy'])->name('profile.destroy');
        Route::patch('user/password', PasswordUpdateController::class)->name('password.update');
    });

    Route::post('email/verification-notification', SendVerificationNotificationController::class)
        ->middleware('throttle:6,1')
        ->name('verification.send');


    require __DIR__ . '/admin.php';
    require __DIR__ . '/user.php';

    // (email OTP endpoints are public and already registered)
});
