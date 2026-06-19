<?php

namespace App\Shared\Enums;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    /**
     * Get all values
     *
     * @return array
     */
    public static function values(): array
    {
        return array_map(fn (self $enum) => $enum->value, self::cases());
    }
}
