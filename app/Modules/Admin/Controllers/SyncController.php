<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Episode;
use App\Models\Series;
use App\Modules\Admin\Services\SupabaseSyncService;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends BaseController
{
    public function __construct(
        private SupabaseSyncService $syncService
    ) {}

    /**
     * POST /api/admin/sync/series
     * Đồng bộ toàn bộ series từ Supabase → DB local
     */
    public function syncSeries(Request $request): JsonResponse
    {
        try {
            $authKey = $request->input('auth_key') ?: $request->input('supabase_auth_key');
            $result = $this->syncService->syncSeries($authKey);

            return $this->success($result, 'Đồng bộ series thành công');
        } catch (\Throwable $e) {
            return $this->error('Lỗi khi đồng bộ series: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/admin/sync/episodes
     * Đồng bộ episodes từ Supabase → DB local
     * Body: { series_id?: string, auth_key?: string }
     */
    public function syncEpisodes(Request $request): JsonResponse
    {
        try {
            $seriesId = $request->input('series_id');
            $authKey = $request->input('auth_key') ?: $request->input('supabase_auth_key');
            $result = $this->syncService->syncEpisodes($seriesId, $authKey);

            return $this->success($result, 'Đồng bộ episodes thành công');
        } catch (\Throwable $e) {
            return $this->error('Lỗi khi đồng bộ episodes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * POST /api/admin/sync/all
     * Đồng bộ cả series lẫn episodes (series trước, episodes sau)
     * Body: { auth_key?: string }
     */
    public function syncAll(Request $request): JsonResponse
    {
        try {
            $authKey = $request->input('auth_key') ?: $request->input('supabase_auth_key');
            $result = $this->syncService->syncAll($authKey);

            return $this->success($result, 'Đồng bộ toàn bộ dữ liệu thành công');
        } catch (\Throwable $e) {
            return $this->error('Lỗi khi đồng bộ toàn bộ: ' . $e->getMessage(), 500);
        }
    }

    /**
     * GET /api/admin/sync/status
     * Kiểm tra số lượng bản ghi hiện có trong DB
     */
    public function status(): JsonResponse
    {
        return $this->success([
            'series_count'   => Series::count(),
            'episodes_count' => Episode::count(),
            'supabase_url'   => env('SUPABASE_URL'),
        ]);
    }
}
