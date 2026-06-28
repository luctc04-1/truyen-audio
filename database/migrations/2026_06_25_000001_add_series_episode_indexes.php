<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm index tối ưu query cho bảng series và episodes.
 *
 * Chạy: php artisan migrate
 *
 * Nếu index đã tồn tại (Duplicate key name) thì bỏ qua an toàn.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ─── episodes ────────────────────────────────────────────────────────
        // Dùng bởi: SeriesController@show & episodes → WHERE series_id ORDER BY episode_number
        $this->safeIndex('episodes', function (Blueprint $table) {
            $table->index(['series_id', 'episode_number'], 'idx_episodes_series_ep_num');
        });

        // Sort "tập mới cập nhật" trên home
        $this->safeIndex('episodes', function (Blueprint $table) {
            $table->index('published_at', 'idx_episodes_published_at');
        });

        // ─── series ──────────────────────────────────────────────────────────
        // Sort "xu hướng / nghe nhiều nhất"
        $this->safeIndex('series', function (Blueprint $table) {
            $table->index('total_listens', 'idx_series_total_listens');
        });

        // Sort "mới nhất"
        $this->safeIndex('series', function (Blueprint $table) {
            $table->index('created_at', 'idx_series_created_at');
        });

        // Sort "đánh giá cao"
        $this->safeIndex('series', function (Blueprint $table) {
            $table->index('average_rating', 'idx_series_average_rating');
        });

        // Filter "hoàn thành / đang cập nhật"
        $this->safeIndex('series', function (Blueprint $table) {
            $table->index('is_complete', 'idx_series_is_complete');
        });

        // Eager-load category (with / where category)
        $this->safeIndex('series', function (Blueprint $table) {
            $table->index('category', 'idx_series_category');
        });

        // Section "nổi bật / được yêu thích"
        $this->safeIndex('series', function (Blueprint $table) {
            $table->index(['is_hot', 'hot_order'], 'idx_series_hot');
        });
    }

    public function down(): void
    {
        $this->safeDrop('episodes', function (Blueprint $table) {
            $table->dropIndex('idx_episodes_series_ep_num');
        });

        foreach ([
            'idx_series_total_listens',
            'idx_series_created_at',
            'idx_series_average_rating',
            'idx_series_is_complete',
            'idx_series_category',
            'idx_series_hot',
        ] as $idx) {
            $this->safeDrop('series', fn (Blueprint $t) => $t->dropIndex($idx));
        }
    }

    // ─── helpers ─────────────────────────────────────────────────────────────

    private function safeIndex(string $table, \Closure $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (\Throwable $e) {
            // Index đã tồn tại (Duplicate key name) — bỏ qua an toàn.
        }
    }

    private function safeDrop(string $table, \Closure $callback): void
    {
        try {
            Schema::table($table, $callback);
        } catch (\Throwable $e) {
            // Index không tồn tại — bỏ qua an toàn.
        }
    }
};
