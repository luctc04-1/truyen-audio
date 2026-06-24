<?php

namespace App\Modules\Admin\Services;

use App\Modules\Admin\Repositories\Contracts\SyncRepositoryInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseSyncService
{
    private string $baseUrl;
    private string $apiKey;
    private string $authKey;
    private int $batchSize;

    public function __construct(
        private readonly SyncRepositoryInterface $syncRepository,
    ) {
        $this->baseUrl = rtrim((string) env('SUPABASE_URL', ''), '/');
        $this->apiKey = (string) env('SUPABASE_API_KEY', '');
        $this->authKey = (string) env('SUPABASE_AUTH_KEY', '');
        $this->batchSize = (int) env('SUPABASE_BATCH_SIZE', 1000);
    }

    // ─── Sync Series ─────────────────────────────────────────────────────────

    /**
     * Lấy series từ Supabase theo trang và upsert vào DB local
     */
    public function syncSeries(): array
    {
        $result = ['total' => 0, 'inserted' => 0, 'updated' => 0];

        $this->fetchPaginated('/rest/v1/series', ['select' => '*'], function (array $page) use (&$result) {
            $mapped = array_map(
                fn (array $item) => array_merge(['id' => $item['id']], $this->mapSeriesData($item)),
                $page
            );

            $upserted = $this->syncRepository->upsertSeries($mapped);
            $result['total'] += count($page);
            $result['inserted'] += $upserted['inserted'];
            $result['updated'] += $upserted['updated'];
        });

        Log::info("[SyncSeries] Tổng: {$result['total']} | Mới: {$result['inserted']} | Cập nhật: {$result['updated']}");

        return $result;
    }

    // ─── Sync Episodes ────────────────────────────────────────────────────────

    /**
     * Lấy episodes từ Supabase theo trang và upsert vào DB local
     *
     * @param string|null $seriesId  Nếu có → chỉ sync episodes của series đó
     */
    public function syncEpisodes(?string $seriesId = null): array
    {
        $params = ['select' => '*'];

        if ($seriesId) {
            $params['series_id'] = "eq.{$seriesId}";
        }

        $result = ['total' => 0, 'inserted' => 0, 'updated' => 0];

        $this->fetchPaginated('/rest/v1/episodes_public', $params, function (array $page) use (&$result) {
            $mapped = array_map(
                fn (array $item) => array_merge(['id' => $item['id']], $this->mapEpisodeData($item)),
                $page
            );

            $upserted = $this->syncRepository->upsertEpisodes($mapped);
            $result['total'] += count($page);
            $result['inserted'] += $upserted['inserted'];
            $result['updated'] += $upserted['updated'];
        });

        Log::info("[SyncEpisodes] Tổng: {$result['total']} | Mới: {$result['inserted']} | Cập nhật: {$result['updated']}");

        return $result;
    }

    // ─── Sync All ─────────────────────────────────────────────────────────────

    /**
     * Sync toàn bộ series rồi episodes
     */
    public function syncAll(): array
    {
        return [
            'series' => $this->syncSeries(),
            'episodes' => $this->syncEpisodes(),
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────────────────

    /**
     * Gọi API Supabase theo trang và xử lý từng trang ngay (không gom hết vào RAM)
     */
    private function fetchPaginated(string $endpoint, array $params, callable $onPage): void
    {
        $offset = 0;
        $limit = $this->batchSize;

        do {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
                'Authorization' => "Bearer {$this->authKey}",
                'Range' => "{$offset}-" . ($offset + $limit - 1),
                'Prefer' => 'count=exact',
            ])->get($this->baseUrl . $endpoint, $params);

            if ($response->failed()) {
                Log::error("[SupabaseSyncService] Lỗi gọi API {$endpoint}: " . $response->body());
                throw new \RuntimeException("Không thể gọi Supabase API: " . $response->status());
            }

            $data = $response->json();

            if (empty($data)) {
                break;
            }

            $onPage($data);
            $offset += $limit;
        } while (count($data) === $limit);
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
            // 'author' => $item['author'] ?? null,
            'narrator' => $item['narrator'] ?? null,
            'is_premium' => $item['is_premium'] ?? false,
            'is_complete' => $item['is_complete'] ?? false,
            'total_episodes' => $item['total_episodes'] ?? 0,
            'total_listens' => $item['total_listens'] ?? 0,
            'average_rating' => $item['average_rating'] ?? null,
            'category' => $item['category'] ?? null,
            'created_at' => $item['created_at'] ?? null,
            'updated_at' => $item['updated_at'] ?? null,
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
            'publish_at' => $item['publish_at'] ?? null,
            'created_at' => $item['created_at'] ?? null,
            'updated_at' => $item['updated_at'] ?? null,
        ];
    }
}
