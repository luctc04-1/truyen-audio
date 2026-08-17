<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SitemapController;

// Dynamic XML Sitemap for Search Engines
Route::get('/sitemap.xml', [SitemapController::class, 'index']);

// Dynamic robots.txt generated using APP_URL / request URL
Route::get('/robots.txt', function () {
    $content = implode("\n", [
        'User-agent: *',
        'Disallow: /admin',
        'Disallow: /profile',
        'Disallow: /history',
        'Disallow: /follows',
        'Disallow: /api/',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ]);

    return response($content, 200)->header('Content-Type', 'text/plain');
});

// Vue SPA Routes - catch-all for frontend routing
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

// Fallback for non-SPA routes (API routes go in routes/api.php)
Route::get('/', function () {
    return view('app');
});

