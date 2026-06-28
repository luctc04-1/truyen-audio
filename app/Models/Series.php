<?php

namespace App\Models;

use App\Support\Eloquent\Concerns\QueriesBooleanColumns;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Series extends Model
{
    use HasUuids, QueriesBooleanColumns;

    protected $table = 'series';

    protected $fillable = [
        'id',
        'category',
        'source_site',
        'source_slug',
        'source_url',
        'slug',
        'title',
        'description',
        'cover_url',
        'author',
        'narrator',
        'is_premium',
        'is_complete',
        'total_episodes',
        'latest_episode_number',
        'total_listens',
        'average_rating',
        'status',
        'is_hot',
        'hot_order',
        'listen_count',
        'published_at',
        'last_crawled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_premium'    => 'boolean',
            'is_complete'   => 'boolean',
            'is_hot'        => 'boolean',
            'average_rating' => 'decimal:2',
            'published_at'  => 'datetime',
            'last_crawled_at' => 'datetime',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────


    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'series_tags', 'series_id', 'tag_id');
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'series_id');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class, 'series_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class, 'series_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'series_id');
    }

    public function seoMeta()
    {
        return $this->hasOne(SeoMeta::class, 'entity_id')
                    ->where('entity_type', 'series');
    }
}
