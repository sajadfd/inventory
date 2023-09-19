<?php

namespace App\Enums;


enum CartTypeEnum: string
{
    case StoreSale='store_sale';
    case InventorySale='inventory_sale';

    public static function getAllValues(): array{
        return array_column(CartTypeEnum::cases() ,'value');
    }

}
