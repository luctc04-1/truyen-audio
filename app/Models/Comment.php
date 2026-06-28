<?php

namespace App\Models;

use App\Support\Eloquent\Casts\PostgresBoolean;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasUuids;

    protected $table = 'comments';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'user_id',
        'series_id',
        'episode_id',
        'parent_id',
        'content',
        'is_pinned',
        'updated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_pinned'  => PostgresBoolean::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeForSeries(Builder $query, string $seriesId): Builder
    {
        return $query
            ->where('series_id', $seriesId)
            ->whereNull('episode_id');
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'episode_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommentLike::class, 'comment_id');
    }
}
