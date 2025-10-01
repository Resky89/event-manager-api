<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\ResponseFormatter;

Route::get('/', function () {
    return ResponseFormatter::success([
        'service' => 'Event Manager API',
        'laravel' => app()->version(),
        'php' => PHP_VERSION,
        'time' => now()->toDateTimeString(),
    ], 'API is up');
});

// Modular API routes
require __DIR__.'/api/auth.php';
require __DIR__.'/api/users.php';
require __DIR__.'/api/events.php';
require __DIR__.'/api/tickets.php';
require __DIR__.'/api/registrations.php';
require __DIR__.'/api/feedback.php';
