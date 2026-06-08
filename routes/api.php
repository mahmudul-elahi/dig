<?php

use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\QuoteLikeController;
use App\Http\Controllers\Auth\EmailOtpController;
use App\Http\Controllers\Auth\ForgotPasswordOtpController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\PasswordUpdateController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\Auth\RefreshTokenController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\Webhook\RevenueCatController;
use Illuminate\Support\Facades\Route;

Route::post('webhook/revenuecat', RevenueCatController::class);

Route::middleware('throttle:5,1')->group(function () {
    Route::post('register', RegisterController::class);
    Route::post('login', LoginController::class);
    Route::post('social/login', SocialLoginController::class);

    Route::post('email/otp/verify', [EmailOtpController::class, 'verify']);
    Route::post('email/otp/resend', [EmailOtpController::class, 'resend']);

    Route::post('password/otp/send', [ForgotPasswordOtpController::class, 'send']);
    Route::post('password/otp/verify', [ForgotPasswordOtpController::class, 'verify']);
    Route::post('password/reset', [ForgotPasswordOtpController::class, 'reset']);
});

// Email verification is handled via the OTP endpoints; legacy signed-link route removed.

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', LogoutController::class);
    Route::post('token/refresh', RefreshTokenController::class);

    Route::get('me', [ProfileController::class, 'show']);
    Route::patch('user', [ProfileController::class, 'update']);
    Route::patch('user/password', PasswordUpdateController::class);

    Route::apiResource('quotes', QuoteController::class);
    Route::post('quotes/{quote}/likes', [QuoteLikeController::class, 'store']);
    Route::delete('quotes/{quote}/likes', [QuoteLikeController::class, 'destroy']);

    require __DIR__.'/admin.php';
    require __DIR__.'/user.php';
});
