<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Controllers\CategoryAdminController;
use App\Modules\Admin\Controllers\CommentAdminController;
use App\Modules\Admin\Controllers\CommunityAdminController;
use App\Modules\Admin\Controllers\DashboardController;
use App\Modules\Admin\Controllers\EpisodeAdminController;
use App\Modules\Admin\Controllers\JobAdminController;
use App\Modules\Admin\Controllers\OrderAdminController;
use App\Modules\Admin\Controllers\PlanAdminController;
use App\Modules\Admin\Controllers\RatingAdminController;
use App\Modules\Admin\Controllers\SeriesAdminController;
use App\Modules\Admin\Controllers\SettingsAdminController;
use App\Modules\Admin\Controllers\SyncController;
use App\Modules\Admin\Controllers\UserAdminController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Community\Controllers\CommunityController;
use App\Modules\Payment\Controllers\OrderController;
use App\Modules\Payment\Controllers\PayOsWebhookController;
use App\Modules\Plan\Controllers\PlanController;
use Illuminate\Broadcasting\BroadcastController;
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
Route::get('/config', [SettingsAdminController::class, 'publicConfig']);

// ─── PayOS webhook (public) ──────────────────────────────────────────────
Route::post('/webhooks/payos', [PayOsWebhookController::class, 'handle']);

// ─── Đơn hàng VIP (client — yêu cầu đăng nhập) ─────────────────────────
Route::middleware('jwt.client')->prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{orderCode}', [OrderController::class, 'show'])->whereNumber('orderCode');
});

// ─── Pusher auth (JWT) ───────────────────────────────────────────────────
Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])
    ->middleware('jwt.client');

// ─── Cộng đồng (đọc công khai, ghi yêu cầu đăng nhập) ────────────────────
Route::middleware('jwt.optional')->prefix('community')->group(function () {
    Route::get('/posts', [CommunityController::class, 'index']);
    Route::get('/posts/{id}/comments', [CommunityController::class, 'comments']);
});

Route::middleware('jwt.client')->prefix('community')->group(function () {
    Route::post('/posts', [CommunityController::class, 'store']);
    Route::patch('/posts/{id}', [CommunityController::class, 'updatePost']);
    Route::delete('/posts/{id}', [CommunityController::class, 'destroyPost']);
    Route::post('/posts/{id}/like', [CommunityController::class, 'toggleLike']);
    Route::post('/posts/{id}/comments', [CommunityController::class, 'storeComment']);
    Route::patch('/community-comments/{id}', [CommunityController::class, 'updateComment']);
    Route::delete('/community-comments/{id}', [CommunityController::class, 'destroyComment']);
    Route::post('/community-comments/{id}/like', [CommunityController::class, 'toggleCommentLike']);
});

// ─── Auth ────────────────────────────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/google', [AuthController::class, 'google']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('jwt.client')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/me', [AuthController::class, 'update']);
        Route::get('/me/history', [AuthController::class, 'history']);
        Route::post('/me/progress', [AuthController::class, 'recordProgress']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me/follows', [FavoriteController::class, 'index']);
        Route::get('/me/follows/series', [FavoriteController::class, 'indexWithSeries']);
    });
});

// ─── Đánh giá & bình luận (client — yêu cầu đăng nhập khi ghi) ──────────
Route::middleware('jwt.client')->group(function () {
    Route::post('/series/{id}/ratings', [RatingController::class, 'store']);
    Route::post('/series/{id}/comments', [CommentController::class, 'store']);
    Route::post('/series/{id}/follow', [FavoriteController::class, 'toggle']);
    Route::post('/comments/{id}/like', [CommentController::class, 'toggleLike']);
    Route::patch('/comments/{id}', [CommentController::class, 'update']);
    Route::patch('/comments/{id}/pin', [CommentController::class, 'pin']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);
});

// ─── Admin (yêu cầu jwt.client + jwt.admin) ──────────────────────────────
Route::middleware(['jwt.client', 'jwt.admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);

    Route::get('/series', [SeriesAdminController::class, 'index']);
    Route::get('/series/{id}', [SeriesAdminController::class, 'show']);
    Route::post('/series', [SeriesAdminController::class, 'store']);
    Route::patch('/series/{id}', [SeriesAdminController::class, 'update']);
    Route::post('/series/{id}/cover', [SeriesAdminController::class, 'uploadCover']);
    Route::delete('/series/{id}', [SeriesAdminController::class, 'destroy']);

    Route::post('/episodes/bulk', [EpisodeAdminController::class, 'bulk']);
    Route::get('/episodes', [EpisodeAdminController::class, 'index']);
    Route::post('/episodes', [EpisodeAdminController::class, 'store']);
    Route::patch('/episodes/{id}', [EpisodeAdminController::class, 'update']);
    Route::post('/episodes/{id}/audio', [EpisodeAdminController::class, 'uploadAudio']);
    Route::delete('/episodes/{id}', [EpisodeAdminController::class, 'destroy']);

    Route::get('/categories', [CategoryAdminController::class, 'index']);
    Route::post('/categories/rename', [CategoryAdminController::class, 'rename']);

    Route::get('/users', [UserAdminController::class, 'index']);
    Route::patch('/users/{id}', [UserAdminController::class, 'update']);
    Route::post('/users/{id}/grant-vip', [UserAdminController::class, 'grantVip']);
    Route::post('/users/{id}/revoke-vip', [UserAdminController::class, 'revokeVip']);

    Route::get('/orders', [OrderAdminController::class, 'index']);
    Route::patch('/orders/{id}', [OrderAdminController::class, 'update']);
    Route::get('/plans', [OrderAdminController::class, 'plans']);
    Route::post('/plans', [PlanAdminController::class, 'store']);
    Route::patch('/plans/{id}', [PlanAdminController::class, 'update']);
    Route::delete('/plans/{id}', [PlanAdminController::class, 'destroy']);

    Route::get('/comments', [CommentAdminController::class, 'index']);
    Route::delete('/comments/{id}', [CommentAdminController::class, 'destroy']);
    Route::patch('/comments/{id}/pin', [CommentAdminController::class, 'pin']);

    Route::get('/ratings', [RatingAdminController::class, 'index']);
    Route::delete('/ratings/{id}', [RatingAdminController::class, 'destroy']);

    Route::get('/community', [CommunityAdminController::class, 'index']);
    Route::get('/community/comments', [CommunityAdminController::class, 'comments']);
    Route::delete('/community/comments/{id}', [CommunityAdminController::class, 'destroyComment']);
    Route::delete('/community/{id}', [CommunityAdminController::class, 'destroy']);

    Route::get('/settings', [SettingsAdminController::class, 'show']);
    Route::patch('/settings', [SettingsAdminController::class, 'update']);

    Route::get('/jobs', [JobAdminController::class, 'index']);

    Route::prefix('sync')->group(function () {
        Route::get('status', [SyncController::class, 'status']);
        Route::post('series', [SyncController::class, 'syncSeries']);
        Route::post('episodes', [SyncController::class, 'syncEpisodes']);
        Route::post('all', [SyncController::class, 'syncAll']);
    });
});
