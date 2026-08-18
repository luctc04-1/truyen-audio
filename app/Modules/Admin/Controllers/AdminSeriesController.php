<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Series;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminSeriesController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Series::query()->withCount(['episodes', 'comments', 'ratings']);

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'ilike', "%{$keyword}%")
                  ->orWhere('author', 'ilike', "%{$keyword}%")
                  ->orWhere('narrator', 'ilike', "%{$keyword}%")
                  ->orWhere('category', 'ilike', "%{$keyword}%");
            });
        }

        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('is_premium')) {
            $query->where('is_premium', filter_var($request->input('is_premium'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('is_hot')) {
            $query->where('is_hot', filter_var($request->input('is_hot'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->filled('status') && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        
        $allowedSorts = ['created_at', 'updated_at', 'total_listens', 'average_rating', 'total_episodes', 'title', 'hot_order'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        } else {
            $query->orderByDesc('created_at');
        }

        $perPage = min(max((int) $request->input('per_page', 15), 1), 100);
        $paginator = $query->paginate($perPage);

        return $this->success([
            'items' => $paginator->items(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    /**
     * Lấy toàn bộ danh sách truyện Hot được sắp xếp theo hot_order ASC, updated_at DESC.
     */
    public function hotList(Request $request): JsonResponse
    {
        $query = Series::query()
            ->withCount(['episodes', 'comments', 'ratings'])
            ->where('is_hot', true);

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'ilike', "%{$keyword}%")
                  ->orWhere('author', 'ilike', "%{$keyword}%")
                  ->orWhere('category', 'ilike', "%{$keyword}%");
            });
        }

        $items = $query->orderBy('hot_order', 'asc')
            ->orderByDesc('updated_at')
            ->get();

        return $this->success([
            'items' => $items,
            'total' => $items->count(),
        ]);
    }

    /**
     * Sắp xếp lại thứ tự truyện Hot hàng loạt.
     */
    public function reorderHot(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|uuid|exists:series,id',
            'items.*.hot_order' => 'nullable|integer|min:1',
        ]);

        $items = $request->input('items');

        DB::transaction(function () use ($items) {
            foreach ($items as $index => $item) {
                $order = isset($item['hot_order']) ? (int) $item['hot_order'] : ($index + 1);
                Series::where('id', $item['id'])->update([
                    'is_hot' => true,
                    'hot_order' => $order,
                ]);
            }
        });

        $updated = Series::query()
            ->withCount(['episodes', 'comments', 'ratings'])
            ->where('is_hot', true)
            ->orderBy('hot_order', 'asc')
            ->orderByDesc('updated_at')
            ->get();

        return $this->success([
            'items' => $updated,
            'total' => $updated->count(),
        ], 'Cập nhật thứ tự truyện Hot thành công');
    }

    /**
     * Gán thứ tự hot cho một truyện cụ thể.
     */
    public function setHotOrder(Request $request, string $id): JsonResponse
    {
        $series = Series::find($id);
        if (!$series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $validated = $request->validate([
            'hot_order' => 'required|integer|min:1',
            'is_hot' => 'nullable|boolean',
        ]);

        $series->hot_order = $validated['hot_order'];
        if (isset($validated['is_hot'])) {
            $series->is_hot = $validated['is_hot'];
        } else {
            $series->is_hot = true;
        }
        $series->save();

        return $this->success($series, 'Cập nhật vị trí truyện Hot thành công');
    }

    public function show(string $id): JsonResponse
    {
        $series = Series::withCount(['episodes', 'comments', 'ratings'])
            ->with(['episodes' => function ($q) {
                $q->orderBy('episode_number', 'asc');
            }])
            ->find($id);

        if (!$series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        return $this->success($series);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:500',
            'slug' => 'nullable|string|max:255|unique:series,slug',
            'category' => 'required|string|max:100',
            'author' => 'nullable|string|max:255',
            'narrator' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cover_url' => 'nullable|string',
            'is_premium' => 'boolean',
            'is_hot' => 'boolean',
            'hot_order' => 'nullable|integer|min:0',
            'is_complete' => 'boolean',
            'status' => 'nullable|string|max:50',
            'total_episodes' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Series::where('slug', $slug)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        if (!empty($validated['is_hot']) && empty($validated['hot_order'])) {
            $maxOrder = Series::where('is_hot', true)->max('hot_order') ?? 0;
            $validated['hot_order'] = $maxOrder + 1;
        }

        $series = Series::create($validated);

        return $this->success($series, 'Tạo truyện thành công', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $series = Series::find($id);

        if (!$series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:500',
            'slug' => 'sometimes|nullable|string|max:255|unique:series,slug,' . $id,
            'category' => 'sometimes|required|string|max:100',
            'author' => 'nullable|string|max:255',
            'narrator' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cover_url' => 'nullable|string',
            'is_premium' => 'boolean',
            'is_hot' => 'boolean',
            'hot_order' => 'nullable|integer|min:0',
            'is_complete' => 'boolean',
            'status' => 'nullable|string|max:50',
            'total_episodes' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['title']) && empty($validated['slug'])) {
            $baseSlug = Str::slug($validated['title']);
            $slug = $baseSlug;
            $counter = 1;
            while (Series::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        if (!empty($validated['is_hot']) && empty($series->hot_order) && empty($validated['hot_order'])) {
            $maxOrder = Series::where('is_hot', true)->max('hot_order') ?? 0;
            $validated['hot_order'] = $maxOrder + 1;
        }

        $series->update($validated);

        return $this->success($series, 'Cập nhật truyện thành công');
    }

    public function toggleHot(string $id): JsonResponse
    {
        $series = Series::find($id);
        if (!$series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $series->is_hot = !$series->is_hot;
        if ($series->is_hot && empty($series->hot_order)) {
            $maxOrder = Series::where('is_hot', true)->max('hot_order') ?? 0;
            $series->hot_order = $maxOrder + 1;
        }
        $series->save();

        return $this->success($series, 'Cập nhật trạng thái Hot thành công');
    }

    public function togglePremium(string $id): JsonResponse
    {
        $series = Series::find($id);
        if (!$series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $series->is_premium = !$series->is_premium;
        $series->save();

        return $this->success($series, 'Cập nhật trạng thái VIP thành công');
    }

    public function destroy(string $id): JsonResponse
    {
        $series = Series::find($id);
        if (!$series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        // Delete episodes associated with the series
        $series->episodes()->delete();
        $series->delete();

        return $this->success(null, 'Đã xóa truyện và toàn bộ tập liên quan');
    }

    public function categories(): JsonResponse
    {
        $categories = Series::select('category')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->groupBy('category')
            ->selectRaw('category, count(*) as count')
            ->orderByDesc('count')
            ->get();

        return $this->success($categories);
    }
}

