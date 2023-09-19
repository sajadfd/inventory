<?php

namespace App\Enums;

enum ProductUnitType
{
    case smaller;
    case larger;

    public static function getAllValues(): array
    {
        return array_column(self::cases(), 'name');
    }
}
