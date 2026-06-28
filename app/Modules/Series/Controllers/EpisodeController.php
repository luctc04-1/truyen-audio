<?php

namespace App\Modules\Series\Controllers;

use App\Models\Episode;
use App\Modules\Series\Support\SeriesPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EpisodeController extends BaseController
{
    private const CACHE_LIST = 'public, max-age=60, stale-while-revalidate=30';

    public function recent(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 5), 30);

        $episodes = Episode::query()
            ->select([
                'id', 'series_id', 'title', 'episode_number',
                'duration_seconds', 'is_premium', 'play_count', 'listen_count',
                'published_at', 'created_at',
            ])
            ->with(['series:id,title,cover_url'])
            ->whereHas('series')
            ->orderByDesc(DB::raw('COALESCE(published_at, created_at)'))
            ->limit($limit)
            ->get()
            ->map(fn (Episode $ep) => SeriesPresenter::episodeWithSeries($ep));

        return $this->success($episodes, 'Tập mới cập nhật')
            ->header('Cache-Control', self::CACHE_LIST);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $episode = Episode::with('series')->find($id);

        if (!$episode) {
            return $this->error('Không tìm thấy tập truyện', 404);
        }

        $canAccessPremium = $request->user()?->isPremium() ?? false;

        return $this->success([
            'episode' => SeriesPresenter::episode($episode, $canAccessPremium),
            'series' => $episode->series
                ? SeriesPresenter::series($episode->series)
                : null,
        ], 'Chi tiết tập');
    }
}
