<?php

namespace Modules\HR\Enums;

use ReflectionClass;

enum HRPermissionEnum
{

    const VIEW_EMPLOYERS = 'view_employers';
    const MODIFY_EMPLOYERS = 'modify_employers';

    const VIEW_CONTRACTS = 'view_contracts';
    const MODIFY_CONTRACTS = 'modify_contracts';

    const VIEW_SALARIES = 'view_salaries';
    const PAY_SALARIES = 'pay_salaries';
    const DELETE_SALARIES = 'delete_salaries';
    const MERGE_SALARIES = 'merge_salaries';

    const VIEW_BONUSES = 'view_bonuses';
    const MODIFY_BONUSES = 'modify_bonuses';

    const VIEW_PENALTIES = 'view_penalties';
    const MODIFY_PENALTIES = 'modify_penalties';

    const VIEW_ABSENCES = 'view_absences';
    const MODIFY_ABSENCES = 'modify_absences';

    const VIEW_ATTENDANCES = 'view_attendances';
    const MODIFY_ATTENDANCES = 'modify_attendances';

    const VIEW_OFF_DATES = 'view_off_dates';
    const MODIFY_OFF_DATES = 'modify_off_dates';

    const VIEW_OFF_WEEK_DAYS = 'view_off_week_days';
    const MODIFY_OFF_WEEK_DAYS = 'modify_off_week_days';

    const VIEW_LOANS = 'view_loans';
    const MODIFY_LOANS = 'modify_loans';

    public static function getAllValues(): array
    {
        $reflectionClass = new ReflectionClass(HRPermissionEnum::class);
        $constants = $reflectionClass->getConstants();
        return array_values($constants);
    }

}
