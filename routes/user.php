<?php

use Illuminate\Support\Facades\Route;

Route::prefix('user')->middleware('role:user')->group(function () {
    Route::get('/', function () {
        return response()->json(['message' => 'Welcome to user area']);
    });
});
