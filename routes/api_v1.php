<?php

declare(strict_types=1);

use App\Http\Api\V1\Controllers\UserController;
use App\Http\Api\V1\Controllers\UserTransactionController;

Route::prefix('v1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('{id}', [UserController::class, 'show']);
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);

        Route::prefix('transaction')->group(function () {
            Route::post('deposit', [UserTransactionController::class, 'deposit']);
            Route::post('withdrawal', [UserTransactionController::class, 'withdrawal']);
            Route::prefix('history')->group(function () {
                Route::get('{id}', [UserTransactionController::class, 'getUserTransactions']);
                Route::get('', [UserTransactionController::class, 'getAllTransactions']);
            });
        });
    });

});
