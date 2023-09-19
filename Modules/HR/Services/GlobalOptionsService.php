<?php

namespace Modules\HR\Services;

use App\Enums\GlobalOptionEnum;
use App\Models\GlobalOption;

class GlobalOptionsService
{
    public static function GetCurrencyValue()
    {
        return GlobalOption::GetCurrencyValue();
    }

    public static function UnifyUnpaidSalaries()
    {
        return GlobalOption::get(GlobalOptionEnum::UnifyUnpaidSalaries, false, false);
    }

    public static function ConsiderAllMonthsAsThirtyDays()
    {
        return GlobalOption::get(GlobalOptionEnum::ConsiderAllMonthsAsThirtyDays, false, false);
    }
}

