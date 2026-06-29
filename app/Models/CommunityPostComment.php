<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommunityPostComment extends Model
{
    use HasUuids;

    protected $table = 'community_post_comments';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'post_id',
        'parent_id',
        'content',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(CommunityPost::class, 'post_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CommunityPostComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(CommunityPostComment::class, 'parent_id');
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CommunityPostCommentLike::class, 'comment_id');
    }
}
