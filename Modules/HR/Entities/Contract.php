<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Modules\HR\Database\factories\ContractFactory;
use Modules\HR\Enums\SalaryTypeEnum;
use Modules\HR\Enums\TrackByEnum;
use Modules\HR\Services\SalariesService;
use OwenIt\Auditing\Contracts\Auditable;
use Spatie\Period\Boundaries;
use Spatie\Period\Period;
use Spatie\Period\Precision;

/**
 * @property-read float $dayWorkEndHour
 * @property-read int[] $offWeekDaysNumbersWithAttendance
 * @property-read int[] $offWeekDaysNumbersWithoutAttendance
 * @method ContractFactory factory(int $count=1)
 * @mixin IdeHelperContract
 */
class Contract extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    public static array $defaultRelations = ['absences', 'attendances', 'offDates', 'offWeekDays', 'bonuses', 'penalties', 'salaries'];

    protected $appends = ['is_ended'];

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
        'salary_price' => 'real',
        'day_work_hours' => 'real',
        'day_work_start_hour' => 'real',
        'salary_type' => SalaryTypeEnum::class,
        "is_active" => "boolean",
        'track_by' => TrackByEnum::class,
    ];

    protected static function newFactory(): ContractFactory
    {
        return ContractFactory::new();
    }

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function offDates(): HasMany
    {
        return $this->hasMany(OffDate::class);
    }

    public function offWeekDays(): HasMany
    {
        return $this->hasMany(OffWeekDay::class);
    }

    public function salaries(): HasMany
    {
        return $this->hasMany(Salary::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(Bonus::class);
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    //Attributes

    public function hoursPeriod(): Attribute
    {
        return Attribute::make(
            get: fn() => Period::make($this->start_date, $this->end_date, Precision::HOUR(), Boundaries::EXCLUDE_END())
        )->shouldCache();
    }

    public function daysPeriod(): Attribute
    {
        return Attribute::make(
            get: fn() => Period::make($this->start_date, $this->end_date, Precision::DAY())
        )->shouldCache();
    }

    protected function dayWorkEndHour(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->day_work_start_hour + $this->day_work_hours
        )->shouldCache();
    }

    protected function offWeekDaysNumbersWithoutAttendance(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->offWeekDays->where('consider_as_attendance', false)->pluck('day')->unique()->toArray()
        )->shouldCache();
    }

    public function offWeekDaysNumbersWithAttendance(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->offWeekDays->where('consider_as_attendance', true)->pluck('day')->unique()->toArray()
        )->shouldCache();
    }

    public function getIsEndedAttribute(): bool
    {
        return $this->is_active === false || $this->end_date < now();
    }

    //Scopes

    public function scopeIsNotEnded(Builder $builder)
    {
        $builder->where('is_active', true)->where('end_date', '>=', now());
    }

    public function hasCleanDates(Builder $builder)
    {
        //Incomplete
        $dateDiffMethodName = config('database.default') === 'sqlite' ? "julianday" : "DATEDIFF";
        $builder->whereHas('absences', function ($query) use ($dateDiffMethodName) {
            $query->whereExists(function ($q2) use ($dateDiffMethodName) {
                $q2->selectRaw(1)
                    ->from('absences AS a2')
                    ->whereRaw('absences.end_date < a2.start_date')
                    ->whereRaw("$dateDiffMethodName(a2.start_date) - $dateDiffMethodName(absences.end_date) > 0");
            });
        });
    }

    //METHODS:
    public function hasUses(): bool
    {
        return $this->absences()->exists() || $this->attendances()->exists()
            || $this->offDates()->exists() || $this->bonuses()->exists()
            || $this->penalties()->exists() || $this->salaries()->exists();
    }

    public function calculateDues($calculateAsOne = false): Collection
    {
        return SalariesService::CalculateContractSalaries($this, $calculateAsOne);
    }
}
