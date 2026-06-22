<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\Admin\Controllers\SyncController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

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
