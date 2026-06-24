<?php

namespace App\Models;

use App\Support\Eloquent\Concerns\QueriesBooleanColumns;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasUuids, QueriesBooleanColumns;

    protected $table = 'subscriptions';

    public $timestamps = false;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'plan_id',
        'order_id',
        'start_at',
        'end_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'start_at'   => 'datetime',
            'end_at'     => 'datetime',
            'is_active'  => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'plan_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function scopeActive($query)
    {
        return $query->whereBoolean('is_active', true);
    }

    public function scopeCurrentlyValid($query)
    {
        return $query->active()
            ->where('start_at', '<=', now())
            ->where('end_at', '>', now());
    }
}
