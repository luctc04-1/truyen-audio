<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Admin\Services\SupabaseSyncService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(
        private SupabaseSyncService $syncService
    ) {
    }

    /**
     * POST /api/admin/sync/series
     * Đồng bộ toàn bộ series từ Supabase → DB local
     */
    public function syncSeries(): JsonResponse
    {
        try {
            $result = $this->syncService->syncSeries();

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ series thành công',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đồng bộ series: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/admin/sync/episodes
     * Đồng bộ episodes từ Supabase → DB local
     * Body: { series_id?: string }  — nếu có thì chỉ sync episodes của series đó
     */
    public function syncEpisodes(Request $request): JsonResponse
    {
        try {
            $seriesId = $request->input('series_id');
            $result = $this->syncService->syncEpisodes($seriesId);

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ episodes thành công',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đồng bộ episodes: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/admin/sync/all
     * Đồng bộ cả series lẫn episodes (series trước, episodes sau)
     */
    public function syncAll(): JsonResponse
    {
        try {
            $result = $this->syncService->syncAll();

            return response()->json([
                'success' => true,
                'message' => 'Đồng bộ toàn bộ dữ liệu thành công',
                'data' => $result,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đồng bộ toàn bộ: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * GET /api/admin/sync/status
     * Kiểm tra số lượng bản ghi hiện có trong DB
     */
    public function status(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'series_count' => \App\Models\Series::count(),
                'episodes_count' => \App\Models\Episode::count(),
                'supabase_url' => env('SUPABASE_URL'),
            ],
        ]);
    }
}
