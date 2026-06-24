<?php

namespace App\Modules\Series\Support;

use App\Models\Episode;
use App\Models\Series;

class SeriesPresenter
{
    /** Map slug frontend ↔ tên thể loại trong cột text `category` (Supabase). */
    private const GENRE_SLUGS = [
        'ngon-tinh'    => 'Ngôn Tình',
        'audio-dai'    => 'Audio Dài',
        'trinh-tham'   => 'Trinh Thám',
        'giang-ho'     => 'Giang Hồ',
        'hoc-duong'    => 'Học Đường',
        'xuyen-khong'  => 'Xuyên Không',
        'roi-nuoc-mac' => 'Rơi Nước Mắt',
        'hai-huoc'     => 'Hài Hước',
        'khoa-hoc'     => 'Khoa Học',
        'kinh-di'      => 'Kinh Dị',
    ];

    /**
     * Dùng cho trang list (home, kho truyện) — không trả description nặng.
     */
    public static function seriesForList(Series $series): array
    {
        return [
            'id'           => $series->id,
            'title'        => $series->title,
            'slug'         => $series->slug,
            'image'        => $series->cover_url ?: self::defaultCover(),
            'genre'        => self::resolveGenre($series->category),
            'genre_label'  => self::resolveGenreLabel($series->category),
            'category'     => $series->category,
            'category_labels' => self::resolveCategoryLabels($series->category),
            'status'       => $series->is_complete ? 'completed' : 'updating',
            'plays'        => self::formatCount($series->total_listens ?? $series->listen_count),
            'plays_raw'    => (int) ($series->total_listens ?? $series->listen_count ?? 0),
            'episodeCount' => (int) ($series->total_episodes ?: $series->episodes_count ?? 0),
            'rating'       => round((float) ($series->average_rating ?? 0), 1),
            'is_premium'   => (bool) $series->is_premium,
            'is_hot'       => (bool) $series->is_hot,
            'hot_order'    => (int) ($series->hot_order ?? 0),
            'created_at'   => optional($series->created_at)->toIso8601String(),
        ];
    }

    /**
     * Dùng cho trang detail — có đầy đủ description, updated_at.
     */
    public static function series(Series $series): array
    {
        return array_merge(self::seriesForList($series), [
            'description' => $series->description ?? '',
            'updated_at'  => optional($series->updated_at)->toIso8601String(),
        ]);
    }

    /**
     * Dùng trong list tập (detail page, queue) — bỏ transcript nặng.
     */
    public static function episodeForList(Episode $episode, bool $canAccessPremium = false): array
    {
        $canPlay = ! $episode->is_premium || $canAccessPremium;

        return [
            'id'             => $episode->id,
            'series_id'      => $episode->series_id,
            'title'          => $episode->title,
            'episode_number' => $episode->episode_number,
            'duration'       => self::formatDuration($episode->duration_seconds),
            'duration_seconds' => (int) ($episode->duration_seconds ?? 0),
            'is_premium'     => (bool) $episode->is_premium,
            'audio_url'      => $canPlay ? ($episode->audio_path ?: $episode->storage_audio_url) : null,
            'play_count'     => (int) ($episode->play_count ?? 0),
        ];
    }

    /**
     * Dùng cho danh sách tập mới (home) — kèm thông tin truyện.
     */
    public static function episodeWithSeries(Episode $episode): array
    {
        $series = $episode->series;
        $plays  = (int) ($episode->play_count ?? $episode->listen_count ?? 0);

        return [
            'id'             => $episode->id,
            'episode_number' => $episode->episode_number,
            'title'          => $episode->title,
            'duration'       => self::formatDuration($episode->duration_seconds),
            'duration_seconds' => (int) ($episode->duration_seconds ?? 0),
            'is_premium'     => (bool) $episode->is_premium,
            'play_count'     => $plays,
            'plays'          => self::formatCount($plays),
            'published_at'   => optional($episode->published_at ?? $episode->created_at)->toIso8601String(),
            'series'         => $series ? [
                'id'    => $series->id,
                'title' => $series->title,
                'image' => $series->cover_url ?: self::defaultCover(),
            ] : null,
        ];
    }

    /**
     * Dùng cho single episode — trả đầy đủ gồm transcript.
     */
    public static function episode(Episode $episode, bool $canAccessPremium = false): array
    {
        return array_merge(self::episodeForList($episode, $canAccessPremium), [
            'transcript' => $episode->transcript,
        ]);
    }

    public static function formatCount(?int $count): string
    {
        $count = (int) ($count ?? 0);

        if ($count >= 1_000_000) {
            return rtrim(rtrim(number_format($count / 1_000_000, 1), '0'), '.') . 'M';
        }

        if ($count >= 1_000) {
            return rtrim(rtrim(number_format($count / 1_000, 1), '0'), '.') . 'K';
        }

        return (string) $count;
    }

    public static function formatDuration(?int $seconds): string
    {
        $seconds = (int) ($seconds ?? 0);

        if ($seconds <= 0) {
            return '0:00';
        }

        $hours   = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);
        $secs    = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $secs);
        }

        return sprintf('%d:%02d', $minutes, $secs);
    }

    private static function defaultCover(): string
    {
        return 'https://images.unsplash.com/photo-1516979187457-637abb4f9353?w=400&q=80';
    }

    public static function resolveGenre(?string $category): string
    {
        if (! $category) {
            return 'all';
        }

        foreach (self::GENRE_SLUGS as $slug => $label) {
            if (stripos($category, $label) !== false) {
                return $slug;
            }
        }

        return 'all';
    }

    public static function resolveGenreLabel(?string $category): ?string
    {
        $labels = self::resolveCategoryLabels($category);

        return $labels[0] ?? null;
    }

    /** @return list<string> */
    public static function resolveCategoryLabels(?string $category): array
    {
        if (! $category) {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $category))));
    }

    /** Slug frontend hoặc tên danh mục → chuỗi lọc cột `category`. */
    public static function categoryFilterTerm(string $value): ?string
    {
        $value = trim($value);
        if ($value === '' || $value === 'all') {
            return null;
        }

        $labels = self::genreLabelsForSlug($value);

        return $labels[0] ?? $value;
    }

    public static function categoryMatchesSlug(?string $category, string $slug): bool
    {
        if ($slug === 'all' || ! $category) {
            return false;
        }

        $label = self::GENRE_SLUGS[$slug] ?? null;

        return $label && stripos($category, $label) !== false;
    }

    /** @return list<string> */
    public static function genreLabelsForSlug(string $slug): array
    {
        if ($slug === 'all') {
            return [];
        }

        $label = self::GENRE_SLUGS[$slug] ?? null;

        return $label ? [$label] : [];
    }
}
