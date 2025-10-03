<?php

use Illuminate\Support\Facades\Route;
use App\Helpers\ResponseFormatter;

Route::get('/', function () {
    return ResponseFormatter::success([
        'service' => 'Event Manager API',
        'laravel' => app()->version(),
        'php' => PHP_VERSION,
        'time' => now()->toDateTimeString(),
    ], 'Welcome to Event Manager API');
});

Route::get('/docs', function () {
    return view('swagger');
});
