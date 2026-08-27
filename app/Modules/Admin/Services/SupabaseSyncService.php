<?php

namespace App\Modules\Admin\Services;

use App\Models\Episode;
use App\Modules\Admin\Repositories\Contracts\SyncRepositoryInterface;
use Illuminate\Http\Client\Pool;
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

    /**
     * Cho phép gán hoặc ghi đè SUPABASE_AUTH_KEY từ UI frontend
     */
    public function setAuthKey(?string $authKey): self
    {
        if ($authKey !== null && trim($authKey) !== '') {
            $this->authKey = trim($authKey);
        }
        return $this;
    }

    // ─── Sync Series ─────────────────────────────────────────────────────────

    /**
     * Lấy series từ Supabase theo trang và upsert vào DB local
     */
    public function syncSeries(?string $authKey = null): array
    {
        $this->setAuthKey($authKey);

        if (empty($this->authKey)) {
            throw new \InvalidArgumentException('Thiếu SUPABASE_AUTH_KEY. Vui lòng nhập key trên giao diện hoặc cấu hình trong .env');
        }

        $result = ['total' => 0, 'inserted' => 0, 'updated' => 0];

        $this->fetchPaginated('/rest/v1/series', ['select' => '*'], function (array $page) use (&$result) {
            $mapped = array_map(
                fn(array $item) => array_merge(['id' => $item['id']], $this->mapSeriesData($item)),
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
     * @param string|null $authKey   Nếu truyền vào → dùng key này thay vì env
     */
    public function syncEpisodes(?string $seriesId = null, ?string $authKey = null): array
    {
        $this->setAuthKey($authKey);

        if (empty($this->authKey)) {
            throw new \InvalidArgumentException('Thiếu SUPABASE_AUTH_KEY. Vui lòng nhập key trên giao diện hoặc cấu hình trong .env');
        }

        $params = ['select' => '*'];

        if ($seriesId) {
            $params['series_id'] = "eq.{$seriesId}";
        }

        $result = ['total' => 0, 'inserted' => 0, 'updated' => 0, 'audio_fetched' => 0, 'audio_missing' => 0];

        $this->fetchPaginated('/rest/v1/episodes_public', $params, function (array $page) use (&$result) {
            $mapped = [];
            $pageEpisodeIds = [];

            foreach ($page as $item) {
                $episodeId = (string) $item['id'];
                $pageEpisodeIds[] = $episodeId;
                $mapped[] = array_merge(['id' => $episodeId], $this->mapEpisodeData($item));
            }

            $existingAudioMap = Episode::query()
                ->whereIn('id', $pageEpisodeIds)
                ->whereNotNull('audio_path')
                ->whereRaw("TRIM(COALESCE(audio_path, '')) <> ''")
                ->pluck('audio_path', 'id')
                ->all();

            $missingEpisodeIds = array_values(array_diff($pageEpisodeIds, array_keys($existingAudioMap)));
            $audioMap = $this->fetchEpisodeAudioUrls($missingEpisodeIds);


            foreach ($mapped as &$episode) {
                $episodeId = $episode['id'];
                $episode['audio_path'] = $existingAudioMap[$episodeId] ?? $audioMap[$episodeId] ?? null;

                if (in_array($episodeId, $missingEpisodeIds, true)) {
                    if ($this->hasAudioPathValue($episode['audio_path'])) {
                        $result['audio_fetched']++;
                    } else {
                        $result['audio_missing']++;
                    }
                }
            }
            unset($episode);

            $upserted = $this->syncRepository->upsertEpisodes($mapped);
            $result['total'] += count($page);
            $result['inserted'] += $upserted['inserted'];
            $result['updated'] += $upserted['updated'];
        });

        Log::info("[SyncEpisodesAudio] Audio fetched: {$result['audio_fetched']} | Audio missing: {$result['audio_missing']}");

        Log::info("[SyncEpisodes] Tổng: {$result['total']} | Mới: {$result['inserted']} | Cập nhật: {$result['updated']}");

        return $result;
    }

    // ─── Sync All ─────────────────────────────────────────────────────────────

    /**
     * Sync toàn bộ series rồi episodes
     * 
     * @param string|null $authKey Nếu truyền vào → dùng key này thay vì env
     */
    public function syncAll(?string $authKey = null): array
    {
        $this->setAuthKey($authKey);

        return [
            'series' => $this->syncSeries($this->authKey),
            'episodes' => $this->syncEpisodes(null, $this->authKey),
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
     * Lấy audio URL cho nhiều episode bằng Http::pool, giữ payload đúng format của edge function hiện tại.
     * Mỗi request vẫn gửi { episodeId: '...' }, nhưng chạy song song để tránh chờ từng episode sequentially.
     *
     * @param  array<int, string>  $episodeIds
     * @return array<string, string>
     */
    private function fetchEpisodeAudioUrls(array $episodeIds): array
    {
        $episodeIds = array_values(array_unique(array_filter($episodeIds, fn($id) => is_string($id) && $id !== '')));

        if ($episodeIds === []) {
            return [];
        }

        $results = Http::pool(fn(Pool $pool) => collect($episodeIds)->map(
            fn(string $id) =>
            $pool->as($id)
                ->timeout(15)
                ->retry(2, 500)
                ->withHeaders([
                    'apikey' => $this->apiKey,
                    'Authorization' => "Bearer {$this->authKey}",
                    'Content-Type' => 'application/json',
                ])
                ->post($this->baseUrl . '/functions/v1/get-audio-url', [
                    'episodeId' => $id,
                ])
        ));

        $audioMap = [];

        foreach ($results as $episodeId => $response) {
            if ($response->failed()) {
                Log::warning("[SupabaseSyncService] Khong the lay audio URL cho episode {$episodeId}: {$response->status()} {$response->body()}");

                continue;
            }

            $data = $response->json();
            $url = $data['audioUrl'] ?? $data['audio_url'] ?? null;

            if ($url) {
                $audioMap[(string) $episodeId] = (string) $url;
            }
        }

        return $audioMap;
    }

    /**
     * Kiểm tra audio_path có giá trị thực sự hay không.
     */
    private function hasAudioPathValue(mixed $audioPath): bool
    {
        if ($audioPath === null) {
            return false;
        }

        if (is_string($audioPath)) {
            return trim($audioPath) !== '';
        }

        return $audioPath !== false && $audioPath !== '';
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
            'transcript' => $item['transcript'] ?? null,
            'publish_at' => $item['publish_at'] ?? null,
            'created_at' => $item['created_at'] ?? null,
            'updated_at' => $item['updated_at'] ?? null,
        ];
    }
}
