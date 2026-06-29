<?php

namespace App\Modules\Series\Controllers;

use App\Models\Episode;
use App\Models\Favorite;
use App\Models\Series;
use App\Modules\Series\Support\SeriesPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeriesController extends BaseController
{
    /**
     * Cột tối thiểu cho list (trang chủ, kho truyện) — bỏ description/transcript.
     */
    private const LIST_COLUMNS = [
        'id', 'category', 'slug', 'title', 'cover_url',
        'author', 'narrator', 'source_site',
        'is_complete', 'is_premium', 'is_hot', 'hot_order',
        'total_episodes', 'total_listens', 'listen_count',
        'average_rating', 'created_at',
    ];

    /**
     * Cột bổ sung cho trang detail (có description).
     */
    private const DETAIL_COLUMNS = [
        'id', 'category', 'slug', 'title', 'cover_url',
        'author', 'narrator', 'source_site', 'description',
        'is_complete', 'is_premium', 'is_hot',
        'total_episodes', 'total_listens', 'listen_count',
        'average_rating', 'created_at', 'updated_at',
    ];

    /**
     * Cột tối thiểu cho list tập (bỏ transcript nặng).
     */
    private const EPISODE_LIST_COLUMNS = [
        'id', 'series_id', 'title', 'episode_number',
        'duration_seconds', 'is_premium', 'audio_path', 'storage_audio_url',
        'play_count',
    ];

    /** Cache list 60s, detail 120s — giảm hit DB cho cùng request. */
    private const CACHE_LIST   = 'public, max-age=60, stale-while-revalidate=30';
    private const CACHE_DETAIL = 'public, max-age=120, stale-while-revalidate=60';

    public function index(Request $request): JsonResponse
    {
        $query = Series::query()
            ->select(self::LIST_COLUMNS)
            ->withCount('episodes');

        if ($search = trim((string) $request->input('search'))) {
            $escaped = addcslashes($search, '%_\\');
            $query->where(function ($q) use ($escaped) {
                $q->where('title', 'ilike', "%{$escaped}%")
                    ->orWhere('narrator', 'ilike', "%{$escaped}%")
                    ->orWhere('author', 'ilike', "%{$escaped}%");
            });
        }

        if ($category = trim((string) $request->input('category'))) {
            $term = SeriesPresenter::categoryFilterTerm($category);
            if ($term !== null) {
                $escaped = addcslashes($term, '%_\\');
                $query->where('category', 'ilike', "%{$escaped}%");
            }
        }

        if ($status = $request->input('status')) {
            $query->whereBoolean('is_complete', $status === 'completed');
        }

        if ($request->boolean('is_hot')) {
            $query->whereBoolean('is_hot', true);
        }

        match ($request->input('sort', 'trending')) {
            'hot'                => $query->orderBy('hot_order')->orderByDesc('total_listens'),
            'newest'             => $query->orderByDesc('created_at'),
            'popular', 'trending' => $query->orderByDesc('total_listens'),
            'rating'             => $query->orderByDesc('average_rating'),
            'az'                 => $query->orderBy('title'),
            default              => $query->orderByDesc('total_listens'),
        };

        $perPage   = min((int) $request->input('per_page', 36), 200);
        $paginator = $query->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (Series $s) => SeriesPresenter::seriesForList($s))
                ->values(),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ], 'Danh sách truyện');
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $series = Series::query()
            ->select(self::DETAIL_COLUMNS)
            ->withCount(['episodes', 'ratings'])
            ->find($id);

        if (! $series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $data             = SeriesPresenter::series($series);
        $data['episodes'] = $this->episodesForRequest($request, $id);

        $user = $request->user();
        if ($user) {
            $data['is_followed'] = Favorite::query()
                ->where('user_id', $user->id)
                ->where('series_id', $id)
                ->exists();
        }

        $response = $this->success($data, 'Chi tiết truyện');

        if ($user) {
            return $response->header('Cache-Control', 'private, no-cache, no-store, must-revalidate');
        }

        return $response->header('Cache-Control', self::CACHE_DETAIL);
    }

    public function episodes(Request $request, string $id): JsonResponse
    {
        if (! Series::whereKey($id)->exists()) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        return $this->success($this->episodesForRequest($request, $id), 'Danh sách tập')
            ->header('Cache-Control', self::CACHE_DETAIL);
    }

    private function episodesForRequest(Request $request, string $seriesId)
    {
        $canAccessPremium = $request->user()?->isPremium() ?? false;

        return Episode::query()
            ->select(self::EPISODE_LIST_COLUMNS)
            ->where('series_id', $seriesId)
            ->orderBy('episode_number')
            ->get()
            ->map(fn (Episode $ep) => SeriesPresenter::episodeForList($ep, $canAccessPremium));
    }
}
