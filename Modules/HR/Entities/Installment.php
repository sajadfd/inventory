<?php

namespace Modules\HR\Entities;

use App\Enums\CurrencyEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperInstallment
 */
class Installment extends Model implements Auditable
{
    use  \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $appends = ['payed_price', 'remaining_price', 'is_payed', 'payment_status'];

    protected $casts = [
        'payed_price' => 'real',
        'price' => 'real',
        'payed_currency' => CurrencyEnum::class,
        'due_date' => 'datetime:Y-m-d H:i:s',
        'payed_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function loan(): BelongsTo
    {
        return $this->belongsTo(Loan::class);
    }

    public function penalty(): BelongsTo
    {
        return $this->belongsTo(Penalty::class);
    }

    public function installmentPayments(): HasMany
    {
        return $this->hasMany(InstallmentPayment::class);
    }

    protected static function boot()
    {
        static::creating(function ($model) {
            $model->code = Str::uuid()->toString();
        });
        parent::boot();
    }

    //Attributes;
    public function payedPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => round($this->penalty?->sum('payed_price') + $this->installmentPayments->sum('price'), 2)
        );
    }

    public function remainingPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->price - $this->payed_price
        );
    }

    public function isPayed(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->price === $this->payed_price
        );
    }

    public function PaymentStatus(): Attribute
    {
        return Attribute::make(
            get: fn() => match ($this->price - $this->payed_price) {
                0, 0.0 => 'payed',
                $this->price => 'not_payed',
                default => 'partially_payed'
            }
        );
    }
}
