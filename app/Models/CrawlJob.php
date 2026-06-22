<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CrawlJob extends Model
{
    use HasUuids;

    protected $table = 'crawl_jobs';

    public $timestamps = false;

    const CREATED_AT = 'started_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'source_site',
        'status',
        'total_series',
        'total_episodes',
        'inserted_series',
        'inserted_episodes',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }
}
