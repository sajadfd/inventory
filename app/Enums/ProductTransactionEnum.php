<?php

namespace App\Enums;

enum ProductTransactionEnum: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
    case Refund = 'refund';
    case Initial = 'initial';
    case Expire = 'expire';
    case ruin = 'ruin';
    case Other = 'other';

    public static function getAllValues(): array
    {
        return array_column(ProductTransactionEnum::cases(), 'value');
    }
}
