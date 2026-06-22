<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UploadJob extends Model
{
    use HasUuids;

    protected $table = 'upload_jobs';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'episode_id',
        'status',
        'retry_count',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'created_at'  => 'datetime',
            'started_at'  => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class, 'episode_id');
    }
}
