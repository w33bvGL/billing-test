<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;

Route::prefix('v1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('{id}', [UserController::class, 'show']);
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
    });
});
