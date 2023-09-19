<?php

namespace App\Enums;

enum SaleType: string
{
    case InventorySale = 'inventory_sale';
    case StoreSale = 'store_sale';

    public static function getAllValues(): array
    {
        return array_column(SaleType::cases(), 'value');
    }
}
