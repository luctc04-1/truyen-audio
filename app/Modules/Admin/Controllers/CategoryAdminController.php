<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Series;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryAdminController extends BaseController
{
    public function index(): JsonResponse
    {
        $categories = Series::query()
            ->select('category', DB::raw('COUNT(*) as series_count'))
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->orderByDesc('series_count')
            ->get()
            ->map(fn ($row) => [
                'name'         => $row->category,
                'series_count' => (int) $row->series_count,
            ]);

        return $this->success($categories);
    }

    public function rename(Request $request): JsonResponse
    {
        $data = $request->validate([
            'from' => 'required|string|max:255',
            'to'   => 'required|string|max:255',
        ]);

        $updated = Series::query()
            ->where('category', $data['from'])
            ->update(['category' => $data['to']]);

        return $this->success(
            ['updated' => $updated],
            "Đã đổi danh mục cho {$updated} truyện"
        );
    }
}
