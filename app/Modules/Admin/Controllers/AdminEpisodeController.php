<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Episode;
use App\Models\Series;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminEpisodeController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Episode::query()->with('series:id,title,category,cover_url');

        if ($request->filled('series_id')) {
            $query->where('series_id', $request->input('series_id'));
        }

        if ($request->filled('search')) {
            $keyword = trim($request->input('search'));
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'ilike', "%{$keyword}%")
                  ->orWhereHas('series', function ($sq) use ($keyword) {
                      $sq->where('title', 'ilike', "%{$keyword}%");
                  });
            });
        }

        if ($request->filled('is_premium')) {
            $query->where('is_premium', filter_var($request->input('is_premium'), FILTER_VALIDATE_BOOLEAN));
        }

        $sortOrder = $request->input('sort_order', 'asc');
        $query->orderBy('episode_number', $sortOrder === 'desc' ? 'desc' : 'asc');

        $perPage = min(max((int) $request->input('per_page', 20), 1), 200);
        $paginator = $query->paginate($perPage);

        return $this->success([
            'items' => $paginator->items(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function show(string $id): JsonResponse
    {
        $episode = Episode::with('series')->find($id);

        if (!$episode) {
            return $this->error('Không tìm thấy tập truyện', 404);
        }

        return $this->success($episode);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'series_id' => 'required|uuid|exists:series,id',
            'title' => 'required|string|max:500',
            'episode_number' => 'required|integer|min:1',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_premium' => 'boolean',
            'transcript' => 'nullable|string',
            'audio_path' => 'nullable|string',
            'storage_audio_url' => 'nullable|string',
        ]);

        $episode = Episode::create($validated);

        // Update series stats
        $series = Series::find($validated['series_id']);
        if ($series) {
            $series->total_episodes = Episode::where('series_id', $series->id)->count();
            $series->latest_episode_number = Episode::where('series_id', $series->id)->max('episode_number');
            $series->save();
        }

        return $this->success($episode, 'Thêm tập thành công', 201);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $episode = Episode::find($id);

        if (!$episode) {
            return $this->error('Không tìm thấy tập truyện', 404);
        }

        $validated = $request->validate([
            'series_id' => 'sometimes|required|uuid|exists:series,id',
            'title' => 'sometimes|required|string|max:500',
            'episode_number' => 'sometimes|required|integer|min:1',
            'duration_seconds' => 'nullable|integer|min:0',
            'is_premium' => 'boolean',
            'transcript' => 'nullable|string',
            'audio_path' => 'nullable|string',
            'storage_audio_url' => 'nullable|string',
        ]);

        $episode->update($validated);

        // Update series stats
        $seriesId = $validated['series_id'] ?? $episode->series_id;
        $series = Series::find($seriesId);
        if ($series) {
            $series->total_episodes = Episode::where('series_id', $series->id)->count();
            $series->latest_episode_number = Episode::where('series_id', $series->id)->max('episode_number');
            $series->save();
        }

        return $this->success($episode, 'Cập nhật tập thành công');
    }

    public function destroy(string $id): JsonResponse
    {
        $episode = Episode::find($id);

        if (!$episode) {
            return $this->error('Không tìm thấy tập truyện', 404);
        }

        $seriesId = $episode->series_id;
        $episode->delete();

        // Update series stats
        $series = Series::find($seriesId);
        if ($series) {
            $series->total_episodes = Episode::where('series_id', $series->id)->count();
            $series->latest_episode_number = Episode::where('series_id', $series->id)->max('episode_number') ?? 0;
            $series->save();
        }

        return $this->success(null, 'Đã xóa tập');
    }
}
