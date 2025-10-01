<?php

use App\Http\Controllers\Api\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::middleware('jwt')->group(function () {
    Route::post('/registrations', [RegistrationController::class, 'store']);
    Route::get('/me/registrations', [RegistrationController::class, 'myRegistrations']);
});
