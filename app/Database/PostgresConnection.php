<?php

namespace App\Database;

use DateTimeInterface;
use Illuminate\Database\PostgresConnection as BasePostgresConnection;

class PostgresConnection extends BasePostgresConnection
{
    /**
     * PostgreSQL requires native boolean bindings; the base connection
     * converts booleans to 0/1 for MySQL compatibility.
     *
     * @param  array<int|string, mixed>  $bindings
     * @return array<int|string, mixed>
     */
    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                // PDO pgsql maps PHP false to '' which is invalid for boolean columns.
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}
