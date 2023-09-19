<?php

namespace App\Enums;

enum UserType: string
{
    case SuperAdmin      = 'super_admin';
    case INVENTORY_ADMIN = 'inventory_admin';
    case Driver          = 'driver';
    case Customer        = 'customer';
    case Other           = 'other';


    public static function getAllValues(): array
    {
        return array_column(UserType::cases(), 'value');
    }


}
