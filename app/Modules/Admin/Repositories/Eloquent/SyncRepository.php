<?php

namespace App\Modules\Admin\Repositories\Eloquent;

use App\Models\Episode;
use App\Models\Series;
use App\Modules\Admin\Repositories\Contracts\SyncRepositoryInterface;
use Illuminate\Support\Carbon;

class SyncRepository implements SyncRepositoryInterface
{
    private int $chunkSize;

    public function __construct()
    {
        $this->chunkSize = (int) env('SUPABASE_DB_CHUNK_SIZE', 500);
    }

    public function upsertSeries(array $seriesData): array
    {
        return $this->upsertInChunks($seriesData, Series::class, self::SERIES_UPDATE_COLUMNS);
    }

    public function upsertEpisodes(array $episodesData): array
    {
        return $this->upsertInChunks($episodesData, Episode::class, self::EPISODE_UPDATE_COLUMNS);
    }

    private function upsertInChunks(array $rows, string $modelClass, array $updateColumns): array
    {
        if ($rows === []) {
            return ['inserted' => 0, 'updated' => 0];
        }

        $inserted = 0;
        $updated = 0;

        foreach (array_chunk($rows, $this->chunkSize) as $chunk) {
            $result = $this->upsertChunk($chunk, $modelClass, $updateColumns);
            $inserted += $result['inserted'];
            $updated += $result['updated'];
        }

        return compact('inserted', 'updated');
    }

    private function upsertChunk(array $chunk, string $modelClass, array $updateColumns): array
    {
        $ids = array_column($chunk, 'id');
        $existingIds = $modelClass::query()
            ->whereIn('id', $ids)
            ->pluck('id')
            ->flip();

        $inserted = 0;
        $updated = 0;
        $now = Carbon::now();

        $rows = [];
        foreach ($chunk as $row) {
            isset($existingIds[$row['id']]) ? $updated++ : $inserted++;

            $rows[] = array_merge($row, [
                'created_at' => $row['created_at'] ?? $now,
                'updated_at' => $now,
            ]);
        }

        $modelClass::upsert($rows, ['id'], $updateColumns);

        return compact('inserted', 'updated');
    }

    private const SERIES_UPDATE_COLUMNS = [
        'slug',
        'title',
        'description',
        'cover_url',
        // 'author',
        'narrator',
        'is_premium',
        'is_complete',
        'total_episodes',
        'total_listens',
        'average_rating',
        'category',
        'created_at',
        'updated_at',
    ];

    private const EPISODE_UPDATE_COLUMNS = [
        'series_id',
        'title',
        'episode_number',
        'duration_seconds',
        'is_premium',
        'play_count',
        'audio_path',
        'transcript',
        'publish_at',
        'created_at',
        'updated_at',
    ];
}
