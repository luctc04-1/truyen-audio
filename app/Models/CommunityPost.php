<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPost extends Model
{
    use HasUuids;

    protected $table = 'community_posts';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    public const TAGS = [
        'thaoluan'  => '🔥 Thảo luận',
        'dexuat'    => '🎧 Đề xuất',
        'hoidap'    => '❓ Hỏi đáp',
        'baoloi'    => '🐞 Báo lỗi',
        'gantruyen' => '📖 Gắn truyện',
    ];

    protected $fillable = [
        'user_id',
        'content',
        'tag',
        'series_id',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function scopeForTag(Builder $query, ?string $tag): Builder
    {
        if (! $tag || $tag === 'all') {
            return $query;
        }

        return $query->where('tag', $tag);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommunityPostLike::class, 'post_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommunityPostComment::class, 'post_id');
    }

    public static function tagLabel(string $tag): string
    {
        return self::TAGS[$tag] ?? self::TAGS['thaoluan'];
    }
}
