<?php

use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/events/{eventId}/tickets', [TicketController::class, 'indexByEvent']);

Route::middleware(['jwt', 'role:organizer,admin'])->group(function () {
    Route::post('/tickets', [TicketController::class, 'store']);
    Route::put('/tickets/{id}', [TicketController::class, 'update']);
    Route::delete('/tickets/{id}', [TicketController::class, 'destroy']);
});
