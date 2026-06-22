<?php

namespace App\Modules\Admin\Repositories\Contracts;

interface SyncRepositoryInterface
{
    /**
     * Upsert danh sách series vào database local
     *
     * @param  array  $seriesData  Mảng dữ liệu series từ API
     * @return array{inserted: int, updated: int}
     */
    public function upsertSeries(array $seriesData): array;

    /**
     * Upsert danh sách episodes vào database local
     *
     * @param  array  $episodesData  Mảng dữ liệu episodes từ API
     * @return array{inserted: int, updated: int}
     */
    public function upsertEpisodes(array $episodesData): array;
}
