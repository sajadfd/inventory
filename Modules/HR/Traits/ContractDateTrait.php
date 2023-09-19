<?php

namespace Modules\HR\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

/**
 * @property-read Period $periodInMinutes
 * @property-read Period $periodInDays
 * @property-read Period $periodInHours
 *
 */
trait ContractDateTrait
{
    protected function periodInHours(): Attribute
    {
        return Attribute::make(
            get: fn() => Period::make($this->start_date, $this->end_date, Precision::HOUR())
        )->shouldCache();
    }

    protected function periodInDays(): Attribute
    {
        return Attribute::make(
            get: fn() => Period::make($this->start_date, $this->end_date, Precision::DAY())
        )->shouldCache();
    }

    protected function periodInMinutes(): Attribute
    {
        return Attribute::make(
            get: fn() => Period::make($this->start_date, $this->end_date, Precision::MINUTE(), Boundaries::EXCLUDE_END())
        )->shouldCache();
    }
}
