<?php

use Illuminate\Support\Facades\Route;

// Admin-only API routes (this file is included from routes/api.php inside the auth middleware group)
Route::prefix('admin')
    ->middleware('role:admin')
    ->group(function () {
        Route::get('/', function () {
            return response()->json(['message' => 'Welcome to admin area']);
        });

        Route::get('stats', function () {
            // placeholder; replace with controller action
            return response()->json(['users' => 0]);
        });
    });
