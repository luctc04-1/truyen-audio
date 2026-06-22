<?php

namespace App\Console\Commands;

use App\Modules\Admin\Services\SupabaseSyncService;
use Illuminate\Console\Command;

class SyncSupabaseData extends Command
{
    /**
     * php artisan sync:supabase {--type=all}
     * type: all | series | episodes
     * Tuỳ chọn: --series-id=<uuid> để sync episodes của 1 series
     */
    protected $signature = 'sync:supabase
                            {--type=all : all | series | episodes}
                            {--series-id= : UUID của series cần sync episodes (chỉ dùng khi --type=episodes)}';

    protected $description = 'Đồng bộ dữ liệu series và episodes từ Supabase API vào database local';

    public function handle(SupabaseSyncService $syncService): int
    {
        $type     = $this->option('type');
        $seriesId = $this->option('series-id');

        $this->info("🔄 Bắt đầu sync [{$type}]...");

        try {
            $result = match ($type) {
                'series'   => $syncService->syncSeries(),
                'episodes' => $syncService->syncEpisodes($seriesId ?: null),
                'all'      => $syncService->syncAll(),
                default    => throw new \InvalidArgumentException("Type không hợp lệ: {$type}"),
            };

            if ($type === 'all') {
                $this->table(
                    ['Bảng', 'Tổng', 'Mới thêm', 'Cập nhật'],
                    [
                        ['series',   $result['series']['total'],   $result['series']['inserted'],   $result['series']['updated']],
                        ['episodes', $result['episodes']['total'],  $result['episodes']['inserted'],  $result['episodes']['updated']],
                    ]
                );
            } else {
                $this->table(
                    ['Bảng', 'Tổng', 'Mới thêm', 'Cập nhật'],
                    [[$type, $result['total'], $result['inserted'], $result['updated']]]
                );
            }

            $this->info('✅ Sync hoàn thành!');
            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('❌ Lỗi: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
