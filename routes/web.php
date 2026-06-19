<?php

use Illuminate\Support\Facades\Route;

// Vue SPA Routes - catch-all for frontend routing
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Fallback for non-SPA routes (API routes go in routes/api.php)
Route::get('/', function () {
    return view('app');
});
