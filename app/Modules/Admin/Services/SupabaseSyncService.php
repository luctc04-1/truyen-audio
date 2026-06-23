<?php

namespace App\Modules\Admin\Services;

use App\Models\Episode;
use App\Models\Series;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseSyncService
{
    private string $baseUrl;
    private string $apiKey;
    private int $batchSize;

    public function __construct()
    {
        $this->baseUrl = rtrim(env('SUPABASE_URL', ''), '/');
        $this->apiKey = env('SUPABASE_API_KEY', '');
        $this->batchSize = (int) env('SUPABASE_BATCH_SIZE', 1000);
    }

    // ─── Sync Series ─────────────────────────────────────────────────────────

    /**
     * Lấy toàn bộ series từ Supabase và lưu vào DB local
     */
    public function syncSeries(): array
    {
        $allSeries = $this->fetchAll('/rest/v1/series', [
            'select' => '*',
        ]);

        $inserted = 0;
        $updated = 0;

        foreach ($allSeries as $item) {
            $exists = Series::where('id', $item['id'])->exists();

            Series::updateOrCreate(
                ['id' => $item['id']],
                $this->mapSeriesData($item)
            );

            $exists ? $updated++ : $inserted++;
        }

        Log::info("[SyncSeries] Tổng: " . count($allSeries) . " | Mới: {$inserted} | Cập nhật: {$updated}");

        return [
            'total' => count($allSeries),
            'inserted' => $inserted,
            'updated' => $updated,
        ];
    }

    // ─── Sync Episodes ────────────────────────────────────────────────────────

    /**
     * Lấy toàn bộ episodes từ Supabase và lưu vào DB local
     *
     * @param string|null $seriesId  Nếu có → chỉ sync episodes của series đó
     */
    public function syncEpisodes(?string $seriesId = null): array
    {
        $params = [
            'select' => '*',
        ];

        if ($seriesId) {
            $params['series_id'] = "eq.{$seriesId}";
        }

        $allEpisodes = $this->fetchAll('/rest/v1/episodes_public', $params);

        $inserted = 0;
        $updated = 0;

        foreach ($allEpisodes as $item) {
            $exists = Episode::where('id', $item['id'])->exists();

            Episode::updateOrCreate(
                ['id' => $item['id']],
                $this->mapEpisodeData($item)
            );

            $exists ? $updated++ : $inserted++;
        }

        Log::info("[SyncEpisodes] Tổng: " . count($allEpisodes) . " | Mới: {$inserted} | Cập nhật: {$updated}");

        return [
            'total' => count($allEpisodes),
            'inserted' => $inserted,
            'updated' => $updated,
        ];
    }

    // ─── Sync All ─────────────────────────────────────────────────────────────

    /**
     * Sync toàn bộ series rồi episodes
     */
    public function syncAll(): array
    {
        $seriesResult = $this->syncSeries();
        $episodesResult = $this->syncEpisodes();

        return [
            'series' => $seriesResult,
            'episodes' => $episodesResult,
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Gọi API Supabase với phân trang tự động (Range header)
     */
    private function fetchAll(string $endpoint, array $params = []): array
    {
        $all = [];
        $offset = 0;
        $limit = $this->batchSize;

        do {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Authorization' => "Bearer {$this->apiKey}",
                'Range' => "{$offset}-" . ($offset + $limit - 1),
                'Prefer' => 'count=exact',
            ])->get($this->baseUrl . $endpoint, array_merge($params));

            if ($response->failed()) {
                Log::error("[SupabaseSyncService] Lỗi gọi API {$endpoint}: " . $response->body());
                throw new \RuntimeException("Không thể gọi Supabase API: " . $response->status());
            }

            $data = $response->json();

            if (empty($data)) {
                break;
            }

            $all = array_merge($all, $data);
            $offset += $limit;

            // Nếu số bản ghi trả về ít hơn limit thì đã hết trang
        } while (count($data) === $limit);

        return $all;
    }

    /**
     * Map dữ liệu series từ Supabase API → fillable của model Series
     */
    private function mapSeriesData(array $item): array
    {
        return [
            'slug' => $item['slug'] ?? null,
            'title' => $item['title'] ?? null,
            'description' => $item['description'] ?? null,
            'cover_url' => $item['cover_url'] ?? null,
            'author' => $item['author'] ?? null,
            'narrator' => $item['narrator'] ?? null,
            'is_premium' => $item['is_premium'] ?? false,
            'is_complete' => $item['is_complete'] ?? false,
            'total_episodes' => $item['total_episodes'] ?? 0,
            'total_listens' => $item['total_listens'] ?? 0,
            'average_rating' => $item['average_rating'] ?? null,
            'published_at' => $item['created_at'] ?? null,
            'source_site' => 'supabase',
            'source_url' => $item['cover_url'] ?? null,
        ];
    }

    /**
     * Map dữ liệu episode từ Supabase API → fillable của model Episode
     */
    private function mapEpisodeData(array $item): array
    {
        return [
            'series_id' => $item['series_id'] ?? null,
            'title' => $item['title'] ?? null,
            'episode_number' => $item['episode_number'] ?? null,
            'duration_seconds' => $item['duration_seconds'] ?? null,
            'is_premium' => $item['is_premium'] ?? false,
            'play_count' => $item['play_count'] ?? 0,
            'audio_path' => $item['audio_path'] ?? null,
            'transcript' => $item['transcript'] ?? null,
            'published_at' => $item['published_at'] ?? null,
            'publish_at' => $item['publish_at'] ?? null,
        ];
    }
}
