<?php

use App\Http\Controllers\Api\EventController;
use Illuminate\Support\Facades\Route;

Route::prefix('events')->group(function () {
    Route::get('/', [EventController::class, 'index']);
    Route::get('/{id}', [EventController::class, 'show']);

    Route::middleware(['jwt', 'role:organizer,admin'])->group(function () {
        Route::post('/', [EventController::class, 'store']);
        Route::put('/{id}', [EventController::class, 'update']);
        Route::delete('/{id}', [EventController::class, 'destroy']);
    });
});
