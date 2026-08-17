<?php

namespace App\Modules\Series\Controllers;

use App\Models\Episode;
use App\Models\Favorite;
use App\Models\Series;
use App\Modules\Series\Support\SeriesPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SeriesController extends BaseController
{
    /**
     * Cột tối thiểu cho list (trang chủ, kho truyện) — bỏ description/transcript.
     */
    private const LIST_COLUMNS = [
        'id', 'category', 'slug', 'title', 'cover_url',
        'author', 'narrator',
        'is_complete', 'is_premium', 'is_hot', 'hot_order',
        'total_episodes', 'total_listens', 'listen_count',
        'average_rating', 'created_at',
    ];

    /**
     * Cột bổ sung cho trang detail (có description).
     */
    private const DETAIL_COLUMNS = [
        'id', 'category', 'slug', 'title', 'cover_url',
        'author', 'narrator', 'description',
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
            ->withCount(['episodes', 'ratings']);

        // Bộ lọc danh mục
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        // Bộ lọc từ khóa tìm kiếm (tên truyện, tác giả, người đọc)
        if ($request->filled('search')) {
            $search = '%' . trim($request->query('search')) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', $search)
                  ->orWhere('author', 'like', $search)
                  ->orWhere('narrator', 'like', $search);
            });
        }

        // Sắp xếp
        $sortBy = $request->query('sort', 'latest');
        match ($sortBy) {
            'popular' => $query->orderByDesc('total_listens'),
            'rating'  => $query->orderByDesc('average_rating'),
            'hot'     => $query->where('is_hot', true)->orderBy('hot_order', 'asc')->orderByDesc('updated_at'),
            default   => $query->orderByDesc('created_at'),
        };

        // Phân trang
        $perPage   = min((int) $request->query('per_page', 12), 50);
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
        $query = Series::query()
            ->select(self::DETAIL_COLUMNS)
            ->withCount(['episodes', 'ratings']);

        // Tìm theo slug trước, fallback theo UUID (tương thích cũ)
        $series = (clone $query)->where('slug', $id)->first();
        if (! $series && Str::isUuid($id)) {
            $series = (clone $query)->find($id);
        }

        if (! $series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $data             = SeriesPresenter::series($series);
        $data['episodes'] = $this->episodesForRequest($request, $series->id);

        $user = $request->user();
        if ($user) {
            $data['is_followed'] = Favorite::query()
                ->where('user_id', $user->id)
                ->where('series_id', $series->id)
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
