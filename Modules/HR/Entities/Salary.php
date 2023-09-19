<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Str;
use Modules\HR\Enums\SalaryTypeEnum;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperSalary
 */
class Salary extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'price' => 'real',
        'currency_value' => 'real',
        'worked_days' => 'real',
        'worked_hours' => 'real',
        'is_payed' => 'boolean',
        'salary_type' => SalaryTypeEnum::class,
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
        'due_date' => 'datetime:Y-m-d H:i:s',
        'payed_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function employer(): HasOneThrough
    {
        return $this->hasOneThrough(Employer::class, Contract::class);
    }

    public function bonuses(): HasMany
    {
        return $this->hasMany(Bonus::class);
    }

    public function absences(): BelongsToMany
    {
        return $this->belongsToMany(Absence::class)->withPivot(['hours']);
    }

    public function attendances(): BelongsToMany
    {
        return $this->belongsToMany(Attendance::class)->withPivot(['hours']);
    }

    public function offDates(): BelongsToMany
    {
        return $this->belongsToMany(OffDate::class)->withPivot(['hours']);
    }

    public function offWeekDays(): BelongsToMany
    {
        return $this->belongsToMany(OffWeekDay::class)->withPivot(['days']);
    }

    public function penalties(): BelongsToMany
    {
        return $this->belongsToMany(Penalty::class)->withPivot(['price']);
    }

    //SCOPES
    public function scopeFullyUsedPenalty($query)
    {
        $query->selectRaw('penalty_salary.salary_id , penalty_salary.penalty_id, SUM(penalty_salary.price) as total_price')
            ->havingRaw('total_price >= penalties.price');
    }

    protected static function newFactory(): \Modules\HR\Database\factories\SalaryFactory
    {
        return \Modules\HR\Database\factories\SalaryFactory::new();
    }

    public function getPriceInIqdAttribute()
    {
        return match ($this->currency) {
            'iqd' => $this->price,
            'usd' => $this->price * $this->currency_value,
        };
    }


    //Boot

    protected static function boot()
    {
        static::creating(function ($model) {
            $model->code = Str::uuid()->toString();
        });
        parent::boot();
    }
}
