<?php

use Illuminate\Support\Facades\Route;

// User-only API routes (this file is included from routes/api.php inside the auth middleware group)
Route::prefix('user')
    ->middleware('role:user')
    ->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Welcome to user area']);
        });

        Route::get('me', [App\Http\Controllers\Auth\ProfileController::class, 'show']);
    });
