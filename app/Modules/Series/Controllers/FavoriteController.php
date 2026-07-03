<?php

namespace App\Modules\Series\Controllers;

use App\Models\Favorite;
use App\Models\Series;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FavoriteController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $ids = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->pluck('series_id')
            ->values();

        return $this->success(['ids' => $ids], 'Danh sách theo dõi');
    }

    public function indexWithSeries(Request $request): JsonResponse
    {
        $items = Favorite::query()
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->with(['series:id,title,cover_url,narrator,author,is_complete,total_episodes,latest_episode_number,average_rating'])
            ->get()
            ->map(fn ($fav) => [
                'id'                    => $fav->series?->id,
                'title'                 => $fav->series?->title,
                'cover_url'             => $fav->series?->cover_url,
                'narrator'              => $fav->series?->narrator,
                'author'                => $fav->series?->author,
                'is_complete'           => $fav->series?->is_complete,
                'total_episodes'        => $fav->series?->total_episodes,
                'latest_episode_number' => $fav->series?->latest_episode_number,
                'average_rating'        => $fav->series?->average_rating
                    ? round((float) $fav->series->average_rating, 1)
                    : null,
                'followed_at'           => $fav->created_at?->format('d/m/Y'),
            ])
            ->filter(fn ($s) => $s['id'] !== null)
            ->values();

        return $this->success(['items' => $items], 'Truyện đang theo dõi');
    }

    public function toggle(Request $request, string $seriesId): JsonResponse
    {
        if (! Series::whereKey($seriesId)->exists()) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $user = $request->user();

        $deleted = Favorite::query()
            ->where('user_id', $user->id)
            ->where('series_id', $seriesId)
            ->delete();

        if ($deleted > 0) {
            return $this->success(['is_followed' => false], 'Đã bỏ theo dõi');
        }

        Favorite::query()->create([
            'user_id'    => $user->id,
            'series_id'  => $seriesId,
            'created_at' => now(),
        ]);

        return $this->success(['is_followed' => true], 'Đã theo dõi truyện', 201);
    }
}
