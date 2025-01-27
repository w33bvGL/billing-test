<?php

declare(strict_types=1);

use App\Http\Controllers\UserController;
use App\Http\Controllers\UserTransactionController;

Route::prefix('v1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('{id}', [UserController::class, 'show']);
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);

        Route::prefix('transaction')->group(function () {
            Route::post('deposit', [UserTransactionController::class, 'deposit']);
            Route::post('withdrawal', [UserTransactionController::class, 'withdrawal']);
            Route::get('history', [UserTransactionController::class, 'history']);
        });
    });
});
