<?php

namespace App\Support\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait QueriesBooleanColumns
{
    public function scopeWhereBoolean(Builder $query, string $column, bool $value): Builder
    {
        $qualified = $query->qualifyColumn($column);

        return $value
            ? $query->whereRaw("{$qualified} IS TRUE")
            : $query->whereRaw("{$qualified} IS FALSE");
    }
}
