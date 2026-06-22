<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Episode extends Model
{
    use HasUuids;

    protected $table = 'episodes';

    protected $fillable = [
        'id',
        'series_id',
        'source_episode_id',
        'title',
        'episode_number',
        'duration_seconds',
        'is_premium',
        'play_count',
        'transcript',
        'audio_path',
        'storage_audio_url',
        'listen_count',
        'published_at',
        'publish_at',
    ];

    protected function casts(): array
    {
        return [
            'is_premium'    => 'boolean',
            'published_at'  => 'datetime',
            'publish_at'    => 'datetime',
            'created_at'    => 'datetime',
            'updated_at'    => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class, 'series_id');
    }

    public function listeningHistories(): HasMany
    {
        return $this->hasMany(ListeningHistory::class, 'episode_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class, 'episode_id');
    }

    public function playlistItems(): HasMany
    {
        return $this->hasMany(PlaylistItem::class, 'episode_id');
    }

    public function uploadJob(): HasOne
    {
        return $this->hasOne(UploadJob::class, 'episode_id');
    }
}
