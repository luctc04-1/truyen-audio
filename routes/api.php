<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Controllers\SyncController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Community\Controllers\CommunityController;
use App\Modules\Plan\Controllers\PlanController;
use App\Modules\Series\Controllers\CommentController;
use App\Modules\Series\Controllers\EpisodeController;
use App\Modules\Series\Controllers\FavoriteController;
use App\Modules\Series\Controllers\RatingController;
use App\Modules\Series\Controllers\SeriesController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ─── Public: Truyện audio (JWT optional — để biết user VIP khi trả audio_url) ─
Route::middleware('jwt.optional')->group(function () {
    Route::prefix('series')->group(function () {
        Route::get('/', [SeriesController::class, 'index']);
        Route::get('/{id}', [SeriesController::class, 'show']);
        Route::get('/{id}/episodes', [SeriesController::class, 'episodes']);
        Route::get('/{id}/ratings', [RatingController::class, 'index']);
        Route::get('/{id}/comments', [CommentController::class, 'index']);
    });

    Route::get('/episodes/recent', [EpisodeController::class, 'recent']);
    Route::get('/episodes/{id}', [EpisodeController::class, 'show']);

    // Alias cho frontend cũ (StoryService gọi /stories)
    Route::get('/stories', [SeriesController::class, 'index']);
    Route::get('/stories/{id}', [SeriesController::class, 'show']);
});

// ─── Public: Gói VIP ─────────────────────────────────────────────────────
Route::get('/plans', [PlanController::class, 'index']);

// ─── Cộng đồng (đọc công khai, ghi yêu cầu đăng nhập) ────────────────────
Route::middleware('jwt.optional')->prefix('community')->group(function () {
    Route::get('/posts', [CommunityController::class, 'index']);
    Route::get('/posts/{id}/comments', [CommunityController::class, 'comments']);
});

Route::middleware('jwt.auth')->prefix('community')->group(function () {
    Route::post('/posts', [CommunityController::class, 'store']);
    Route::post('/posts/{id}/like', [CommunityController::class, 'toggleLike']);
    Route::post('/posts/{id}/comments', [CommunityController::class, 'storeComment']);
    Route::post('/community-comments/{id}/like', [CommunityController::class, 'toggleCommentLike']);
});

// ─── Auth ────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/google', [AuthController::class, 'google']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me/follows', [FavoriteController::class, 'index']);
    });
});

// ─── Đánh giá & bình luận (yêu cầu đăng nhập khi ghi) ───────────────────
Route::middleware('jwt.auth')->group(function () {
    Route::post('/series/{id}/ratings', [RatingController::class, 'store']);
    Route::post('/series/{id}/comments', [CommentController::class, 'store']);
    Route::post('/series/{id}/follow', [FavoriteController::class, 'toggle']);
    Route::post('/comments/{id}/like', [CommentController::class, 'toggleLike']);
    Route::patch('/comments/{id}', [CommentController::class, 'update']);
    Route::patch('/comments/{id}/pin', [CommentController::class, 'pin']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});

// ─── Admin: Đồng bộ dữ liệu từ Supabase ──────────────────────────────────
Route::prefix('admin/sync')->group(function () {

    // Kiểm tra trạng thái DB hiện tại
    Route::get('status', [SyncController::class, 'status']);

    // Sync chỉ series
    Route::post('series', [SyncController::class, 'syncSeries']);

    // Sync chỉ episodes  (body: { series_id?: string })
    Route::post('episodes', [SyncController::class, 'syncEpisodes']);

    // Sync tất cả (series → episodes)
    Route::post('all', [SyncController::class, 'syncAll']);
});
