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
