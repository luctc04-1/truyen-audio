<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasUuids;

    protected $table = 'system_settings';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'setting_key',
        'setting_value',
    ];

    protected function casts(): array
    {
        return [
            'setting_value' => 'array', // JSONB -> PHP array
        ];
    }
}
