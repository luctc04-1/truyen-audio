<?php

namespace App\Modules\Admin\Controllers;

use App\Models\Episode;
use App\Models\Series;
use App\Models\UploadJob;
use App\Modules\Admin\Support\AdminPresenter;
use App\Modules\Admin\Support\AdminSearch;
use App\Shared\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EpisodeAdminController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $query = Episode::query()->with('series:id,title');

        if ($seriesId = $request->input('series_id')) {
            $query->where('series_id', $seriesId);
        }

        if ($search = trim((string) $request->input('search'))) {
            $escaped = AdminSearch::escape($search);
            $query->where(function ($q) use ($escaped) {
                $q->where('title', 'ilike', "%{$escaped}%")
                    ->orWhereHas('series', fn ($sq) => $sq->where('title', 'ilike', "%{$escaped}%"));
            });
        }

        if ($request->has('is_premium')) {
            $query->where('is_premium', $request->boolean('is_premium'));
        }

        if ($request->boolean('missing_audio')) {
            $query->where(function ($q) {
                $q->whereNull('audio_path')->whereNull('storage_audio_url');
            });
        }

        $perPage   = min((int) $request->input('per_page', 20), 100);
        $paginator = $query->orderByDesc('created_at')->paginate($perPage);

        return $this->success([
            'items'      => $paginator->getCollection()
                ->map(fn (Episode $ep) => AdminPresenter::episode($ep))
                ->values(),
            'pagination' => $this->paginationMeta($paginator),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'series_id'      => 'required|uuid|exists:series,id',
            'title'          => 'required|string|max:500',
            'episode_number' => 'required|integer|min:0',
            'is_premium'     => 'sometimes|boolean',
            'transcript'     => 'nullable|string',
            'duration_seconds' => 'nullable|integer|min:0',
            'publish_at'     => 'nullable|date',
        ]);

        $publishAt = isset($data['publish_at']) ? $data['publish_at'] : now();

        $episode = Episode::create([
            'series_id'        => $data['series_id'],
            'title'            => $data['title'],
            'episode_number'   => $data['episode_number'],
            'is_premium'       => $data['is_premium'] ?? false,
            'transcript'       => $data['transcript'] ?? null,
            'duration_seconds' => $data['duration_seconds'] ?? 0,
            'publish_at'       => $publishAt,
            'published_at'     => ($publishAt && strtotime($publishAt) <= time()) ? $publishAt : null,
        ]);

        $this->refreshSeriesEpisodeCount($data['series_id']);

        return $this->success(
            AdminPresenter::episode($episode->load('series:id,title')),
            'Đã tạo tập mới',
            201
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $episode = Episode::with('series:id,title')->find($id);

        if (! $episode) {
            return $this->error('Không tìm thấy tập', 404);
        }

        $data = $request->validate([
            'title'            => 'sometimes|string|max:500',
            'episode_number'   => 'sometimes|integer|min:0',
            'is_premium'       => 'sometimes|boolean',
            'transcript'       => 'nullable|string',
            'duration_seconds' => 'sometimes|integer|min:0',
            'publish_at'       => 'nullable|date',
        ]);

        if (array_key_exists('publish_at', $data)) {
            $data['published_at'] = ($data['publish_at'] && strtotime($data['publish_at']) <= time())
                ? $data['publish_at']
                : null;
        }

        $episode->fill($data)->save();

        return $this->success(
            AdminPresenter::episode($episode->fresh()->load('series:id,title')),
            'Cập nhật tập thành công'
        );
    }

    public function uploadAudio(Request $request, string $id): JsonResponse
    {
        $episode = Episode::with('series:id,title')->find($id);

        if (! $episode) {
            return $this->error('Không tìm thấy tập', 404);
        }

        $request->validate([
            'audio' => 'required|file|mimes:mp3,m4a,wav,ogg|max:102400',
        ]);

        $file = $request->file('audio');
        $path = $file->store("audio/{$episode->series_id}", 'public');
        $url  = Storage::disk('public')->url($path);

        $episode->audio_path        = $url;
        $episode->storage_audio_url = $url;
        $episode->save();

        UploadJob::create([
            'episode_id' => $episode->id,
            'status'     => 'completed',
            'started_at' => now(),
            'finished_at'=> now(),
        ]);

        return $this->success(
            AdminPresenter::episode($episode->fresh()->load('series:id,title')),
            'Đã upload file audio'
        );
    }

    public function destroy(string $id): JsonResponse
    {
        $episode = Episode::find($id);

        if (! $episode) {
            return $this->error('Không tìm thấy tập', 404);
        }

        $seriesId = $episode->series_id;
        $episode->delete();

        $this->refreshSeriesEpisodeCount($seriesId);

        return $this->success(null, 'Đã xóa tập');
    }

    public function bulk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'action' => 'required|string|in:delete,set_premium,unset_premium',
            'ids'    => 'required|array|min:1|max:100',
            'ids.*'  => 'uuid|exists:episodes,id',
        ]);

        $episodes = Episode::whereIn('id', $data['ids'])->get();
        $seriesIds = $episodes->pluck('series_id')->unique();

        if ($data['action'] === 'delete') {
            Episode::whereIn('id', $data['ids'])->delete();
            foreach ($seriesIds as $seriesId) {
                $this->refreshSeriesEpisodeCount($seriesId);
            }

            return $this->success(['deleted' => count($data['ids'])], 'Đã xóa các tập đã chọn');
        }

        $value = $data['action'] === 'set_premium';
        Episode::whereIn('id', $data['ids'])->update(['is_premium' => $value]);

        return $this->success(['updated' => count($data['ids'])], $value ? 'Đã đặt VIP' : 'Đã bỏ VIP');
    }

    private function refreshSeriesEpisodeCount(string $seriesId): void
    {
        Series::whereKey($seriesId)->update([
            'total_episodes' => Episode::where('series_id', $seriesId)->count(),
        ]);
    }
}
