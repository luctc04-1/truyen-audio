<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Episode;
use App\Models\Series;
use App\Modules\Admin\Support\AdminPresenter;
use App\Modules\Admin\Support\AdminSearch;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SeriesAdminController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Series::query()->withCount('episodes');

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('title', 'ilike', "%{$escaped}%")
                    ->orWhere('author', 'ilike', "%{$escaped}%")
                    ->orWhere('narrator', 'ilike', "%{$escaped}%");
            });
        }

        if ($request->has('is_hot')) {
            $query->whereBoolean('is_hot', $request->boolean('is_hot'));
        }

        if ($request->has('is_premium')) {
            $query->whereBoolean('is_premium', $request->boolean('is_premium'));
        }

        if ($category = trim((string) $request->input('category'))) {
            $escaped = AdminSearch::escape($category);
            $query->where('category', 'ilike', "%{$escaped}%");
        }

        $perPage   = min((int) $request->input('per_page', 20), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (Series $s) => AdminPresenter::series($s))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $series = Series::query()->withCount('episodes')->find($id);

        if (! $series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        return $this->success(AdminPresenter::series($series));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title'       => 'required|string|max:500',
            'slug'        => 'nullable|string|max:500|unique:series,slug',
            'author'      => 'nullable|string|max:255',
            'narrator'    => 'nullable|string|max:255',
            'category'    => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_premium'  => 'sometimes|boolean',
            'is_hot'      => 'sometimes|boolean',
        ]);

        $slug = $data['slug'] ?? Str::slug($data['title']);
        $base = $slug;
        $i    = 1;
        while (Series::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        $series = Series::create([
            'title'       => $data['title'],
            'slug'        => $slug,
            'author'      => $data['author'] ?? null,
            'narrator'    => $data['narrator'] ?? null,
            'category'    => $data['category'] ?? null,
            'description' => $data['description'] ?? null,
            'is_premium'  => $data['is_premium'] ?? false,
            'is_hot'      => $data['is_hot'] ?? false,
            'status'      => 'draft',
            'published_at'=> now(),
        ]);

        return $this->success(
            AdminPresenter::series($series->loadCount('episodes')),
            'Đã tạo truyện mới',
            201
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $series = Series::find($id);

        if (! $series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $data = $request->validate([
            'title'        => 'sometimes|string|max:500',
            'slug'         => 'sometimes|string|max:500|unique:series,slug,' . $id,
            'author'       => 'nullable|string|max:255',
            'narrator'     => 'nullable|string|max:255',
            'category'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'is_premium'   => 'sometimes|boolean',
            'is_complete'  => 'sometimes|boolean',
            'is_hot'       => 'sometimes|boolean',
            'hot_order'    => 'sometimes|integer|min:0',
            'status'       => 'nullable|string|max:50',
            'cover_url'    => 'nullable|string|max:1000',
        ]);

        $series->fill($data)->save();

        return $this->success(
            AdminPresenter::series($series->fresh()->loadCount('episodes')),
            'Cập nhật truyện thành công'
        );
    }

    public function uploadCover(Request $request, string $id): JsonResponse
    {
        $series = Series::find($id);

        if (! $series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        $request->validate([
            'cover' => 'required|image|max:5120',
        ]);

        $path = $request->file('cover')->store("covers/{$series->id}", 'public');
        $series->cover_url = Storage::disk('public')->url($path);
        $series->save();

        return $this->success(
            AdminPresenter::series($series->fresh()->loadCount('episodes')),
            'Đã upload ảnh bìa'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $series = Series::find($id);

        if (! $series) {
            return $this->error('Không tìm thấy truyện', 404);
        }

        Episode::where('series_id', $series->id)->delete();
        $series->delete();

        return $this->success(null, 'Đã xóa truyện');
    }
}
