<?php

namespace Modules\HR\Enums;

enum SalaryTypeEnum: string
{
//    case ByHour = "by_hour";
    case ByDay = "by_day";
    case ByMonth = "by_month";

    public static function getAllValues(): array
    {
        return array_column(SalaryTypeEnum::cases(), 'value');
    }
}
