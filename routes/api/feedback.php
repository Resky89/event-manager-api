<?php

use App\Http\Controllers\Api\FeedbackController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->post('/feedback', [FeedbackController::class, 'store']);
Route::get('/events/{eventId}/feedback', [FeedbackController::class, 'listByEvent']);
