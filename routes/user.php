<?php

use App\Http\Controllers\User\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->middleware('role:user')->group(function () {
    Route::get('notifications', [NotificationController::class, 'show']);
    Route::patch('notifications', [NotificationController::class, 'update']);
});
