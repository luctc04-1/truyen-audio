<?php

namespace App\Modules\Series\Controllers;

use App\Models\Rating;
use App\Models\Series;
use App\Modules\Series\Support\UserPresenter;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatingController extends BaseController
{
    public function index(Request $request, string $seriesId): JsonResponse
    {
        if (! Series::whereKey($seriesId)->exists()) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $perPage = min((int) $request->input('per_page', 20), 50);
        $paginator = Rating::query()
            ->where('series_id', $seriesId)
            ->with('user')
            ->orderByDesc('created_at')
            ->paginate($perPage);

        $userRating = null;
        $user = $request->user();

        if ($user) {
            $own = Rating::query()
                ->where('series_id', $seriesId)
                ->where('user_id', $user->id)
                ->first();

            if ($own) {
                $userRating = $this->formatRating($own);
            }
        }

        $average = Rating::query()
            ->where('series_id', $seriesId)
            ->avg('rating');

        return $this->success([
            'summary' => [
                'average' => round((float) ($average ?? 0), 1),
                'count'   => $paginator->total(),
            ],
            'user_rating' => $userRating,
            'items'       => $paginator->getCollection()
                ->map(fn (Rating $rating) => $this->formatRating($rating))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ], 'Danh sách đánh giá');
    }

    public function store(Request $request, string $seriesId): JsonResponse
    {
        if (! Series::whereKey($seriesId)->exists()) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'content' => 'nullable|string|max:1000',
        ]);

        $user = $request->user();
        $content = isset($data['content']) ? trim($data['content']) : null;
        $content = $content === '' ? null : $content;

        $rating = Rating::updateOrCreate(
            [
                'user_id'   => $user->id,
                'series_id' => $seriesId,
            ],
            [
                'rating'  => $data['rating'],
                'content' => $content,
            ]
        );

        $isUpdate = ! $rating->wasRecentlyCreated;

        if ($isUpdate) {
            $rating->update(['updated_at' => now()]);
        }

        $this->syncSeriesAverageRating($seriesId);

        $rating->load('user');

        return $this->success(
            $this->formatRating($rating),
            $isUpdate ? 'Cập nhật đánh giá thành công' : 'Đánh giá thành công',
            $isUpdate ? 200 : 201
        );
    }

    private function syncSeriesAverageRating(string $seriesId): void
    {
        $average = Rating::query()
            ->where('series_id', $seriesId)
            ->avg('rating');

        Series::whereKey($seriesId)->update([
            'average_rating' => $average !== null ? round((float) $average, 2) : null,
        ]);
    }

    private function formatRating(Rating $rating): array
    {
        $rating->loadMissing('user');

        return [
            'id'         => $rating->id,
            'rating'     => (int) $rating->rating,
            'content'    => $rating->content,
            'created_at' => $rating->created_at?->toIso8601String(),
            'updated_at' => $rating->updated_at?->toIso8601String(),
            'user'       => $rating->user
                ? UserPresenter::forPublic($rating->user)
                : null,
        ];
    }
}
