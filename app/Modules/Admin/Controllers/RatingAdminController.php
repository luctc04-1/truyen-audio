<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Rating;
use App\Modules\Admin\Support\AdminPresenter;
use App\Modules\Admin\Support\AdminSearch;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RatingAdminController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Rating::query()
            ->with(['user:id,username,email', 'series:id,title']);

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('content', 'ilike', "%{$escaped}%")
                    ->orWhereHas('series', fn ($sq) => $sq->where('title', 'ilike', "%{$escaped}%"));
            });
        }

        if ($rating = $request->input('rating')) {
            $query->where('rating', (int) $rating);
        }

        if ($seriesId = $request->input('series_id')) {
            $query->where('series_id', $seriesId);
        }

        $perPage   = min((int) $request->input('per_page', 15), 50);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        $stats = [
            'total'    => Rating::count(),
            'average'  => round((float) Rating::avg('rating'), 1),
            'by_star'  => Rating::query()
                ->select('rating', DB::raw('COUNT(*) as count'))
                ->groupBy('rating')
                ->orderByDesc('rating')
                ->pluck('count', 'rating'),
        ];

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (Rating $r) => AdminPresenter::rating($r))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
            'stats'      => $stats,
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $rating = Rating::find($id);

        if (! $rating) {
            return $this->error('Không tìm thấy đánh giá', 404);
        }

        $rating->delete();

        return $this->success(null, 'Đã xóa đánh giá');
    }
}
