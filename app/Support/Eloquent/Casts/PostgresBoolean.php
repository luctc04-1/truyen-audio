<?php

namespace App\Support\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * PostgreSQL rejects integer 0/1 bindings for boolean columns.
 * This cast persists booleans as 'true'/'false' strings on pgsql.
 */
class PostgresBoolean implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);

        if ($model->getConnection()->getDriverName() === 'pgsql') {
            return $bool ? 'true' : 'false';
        }

        return $bool;
    }
}
