<?php

namespace App\Modules\Admin\Support;

class AdminSearch
{
    public static function escape(string $term): string
    {
        return addcslashes(trim($term), '%_\\');
    }
}
