<?php

namespace App\Enums;

enum CurrencyEnum : string
{
    case Usd = 'usd';
    case Iqd = 'iqd';

    public static function getAllValues(): array
    {
        return array_column(CurrencyEnum::cases(), 'value');
    }
}