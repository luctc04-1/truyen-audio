<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListeningHistory extends Model
{
    use HasUuids;

    protected $table = 'listening_histories';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'episode_id',
        'listened_seconds',
        'completed',
        'last_listened_at',
    ];

    protected function casts(): array
    {
        return [
            'completed'        => 'boolean',
            'last_listened_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'episode_id');
    }
}
