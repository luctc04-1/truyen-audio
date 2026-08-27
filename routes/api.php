<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Controllers\AdminCommunityController;
use App\Modules\Admin\Controllers\AdminDashboardController;
use App\Modules\Admin\Controllers\AdminEpisodeController;
use App\Modules\Admin\Controllers\AdminOrderController;
use App\Modules\Admin\Controllers\AdminPlanController;
use App\Modules\Admin\Controllers\AdminSeriesController;
use App\Modules\Admin\Controllers\AdminSettingController;
use App\Modules\Admin\Controllers\AdminUserController;
use App\Modules\Admin\Controllers\SyncController;
use App\Modules\Auth\Controllers\AuthController;
use App\Modules\Community\Controllers\CommunityController;
use App\Modules\Notification\Controllers\NotificationController;
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

// ─── PayOS webhook (public) ──────────────────────────────────────────────
Route::post('/webhooks/payos', [PayOsWebhookController::class, 'handle']);

// ─── Đơn hàng VIP (yêu cầu đăng nhập) ────────────────────────────────────
Route::middleware('jwt.auth')->prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{orderCode}', [OrderController::class, 'show'])->whereNumber('orderCode');
});

// ─── Pusher auth (JWT) ───────────────────────────────────────────────────
Route::post('/broadcasting/auth', [BroadcastController::class, 'authenticate'])
    ->middleware('jwt.auth');

// ─── Cộng đồng (đọc công khai, ghi yêu cầu đăng nhập) ────────────────────
Route::middleware('jwt.optional')->prefix('community')->group(function () {
    Route::get('/posts', [CommunityController::class, 'index']);
    Route::get('/posts/{id}/comments', [CommunityController::class, 'comments']);
});

Route::middleware('jwt.auth')->prefix('community')->group(function () {
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
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::patch('/me', [AuthController::class, 'update']);
        Route::get('/me/history', [AuthController::class, 'history']);
        Route::post('/me/progress', [AuthController::class, 'recordProgress']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me/follows', [FavoriteController::class, 'index']);
        Route::get('/me/follows/series', [FavoriteController::class, 'indexWithSeries']);
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

// ─── Thông báo (yêu cầu đăng nhập) ──────────────────────────────────────
Route::middleware('jwt.auth')->prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index']);
    Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
    Route::patch('/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/{id}', [NotificationController::class, 'destroy']);
    Route::delete('/', [NotificationController::class, 'destroyAll']);
});

// ─── Web Push Subscriptions (hỗ trợ cả khách & thành viên) ───────────────
Route::middleware('jwt.optional')->prefix('notifications')->group(function () {
    Route::post('/push-subscribe', [NotificationController::class, 'subscribePush']);
    Route::post('/push-unsubscribe', [NotificationController::class, 'unsubscribePush']);
});

// ─── Admin Management API ───────────────────────────────────────────────
Route::prefix('admin')->group(function () {
    // Dashboard Stats
    Route::get('dashboard/stats', [AdminDashboardController::class, 'stats']);

    // Series Management
    Route::prefix('series')->group(function () {
        Route::get('/', [AdminSeriesController::class, 'index']);
        Route::get('/categories', [AdminSeriesController::class, 'categories']);
        Route::get('/hot', [AdminSeriesController::class, 'hotList']);
        Route::post('/reorder-hot', [AdminSeriesController::class, 'reorderHot']);
        Route::post('/{id}/set-hot-order', [AdminSeriesController::class, 'setHotOrder']);
        Route::get('/{id}', [AdminSeriesController::class, 'show']);
        Route::post('/', [AdminSeriesController::class, 'store']);
        Route::patch('/{id}', [AdminSeriesController::class, 'update']);
        Route::post('/{id}/toggle-hot', [AdminSeriesController::class, 'toggleHot']);
        Route::post('/{id}/toggle-premium', [AdminSeriesController::class, 'togglePremium']);
        Route::delete('/{id}', [AdminSeriesController::class, 'destroy']);
    });

    // Episodes Management
    Route::prefix('episodes')->group(function () {
        Route::get('/', [AdminEpisodeController::class, 'index']);
        Route::get('/{id}', [AdminEpisodeController::class, 'show']);
        Route::post('/', [AdminEpisodeController::class, 'store']);
        Route::patch('/{id}', [AdminEpisodeController::class, 'update']);
        Route::delete('/{id}', [AdminEpisodeController::class, 'destroy']);
    });

    // Users Management
    Route::prefix('users')->group(function () {
        Route::get('/', [AdminUserController::class, 'index']);
        Route::get('/{id}', [AdminUserController::class, 'show']);
        Route::post('/{id}/toggle-admin', [AdminUserController::class, 'toggleAdmin']);
        Route::post('/{id}/grant-vip', [AdminUserController::class, 'grantVip']);
    });

    // Orders Management
    Route::prefix('orders')->group(function () {
        Route::get('/', [AdminOrderController::class, 'index']);
        Route::patch('/{id}/status', [AdminOrderController::class, 'updateStatus']);
    });

    // Plans Management
    Route::prefix('plans')->group(function () {
        Route::get('/', [AdminPlanController::class, 'index']);
        Route::post('/', [AdminPlanController::class, 'store']);
        Route::patch('/{id}', [AdminPlanController::class, 'update']);
        Route::delete('/{id}', [AdminPlanController::class, 'destroy']);
    });

    // Community & Comments Moderation
    Route::prefix('community')->group(function () {
        Route::get('/stats', [AdminCommunityController::class, 'stats']);
        Route::get('/posts', [AdminCommunityController::class, 'posts']);
        Route::get('/posts/{id}', [AdminCommunityController::class, 'postDetail']);
        Route::delete('/posts/{id}', [AdminCommunityController::class, 'destroyPost']);
        Route::post('/posts/batch-delete', [AdminCommunityController::class, 'batchDestroyPosts']);
        Route::get('/comments', [AdminCommunityController::class, 'comments']);
        Route::delete('/comments/{id}', [AdminCommunityController::class, 'destroyComment']);
        Route::post('/comments/batch-delete', [AdminCommunityController::class, 'batchDestroyComments']);
    });

    // Comments Moderation (Legacy / Alias)
    Route::prefix('comments')->group(function () {
        Route::get('/', [AdminCommunityController::class, 'comments']);
        Route::delete('/{id}', [AdminCommunityController::class, 'destroyComment']);
        Route::post('/batch-delete', [AdminCommunityController::class, 'batchDestroyComments']);
    });

    // System & SEO Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [AdminSettingController::class, 'index']);
        Route::post('/', [AdminSettingController::class, 'update']);
    });

    // Sync & Crawler
    Route::prefix('sync')->group(function () {
        Route::get('status', [SyncController::class, 'status']);
        Route::post('series', [SyncController::class, 'syncSeries']);
        Route::post('episodes', [SyncController::class, 'syncEpisodes']);
        Route::post('all', [SyncController::class, 'syncAll']);
    });
});
