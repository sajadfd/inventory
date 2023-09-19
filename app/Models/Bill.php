<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperBill
 */
class Bill extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable, CreatedByTrait;

    protected $guarded = [];

    protected $casts = [
        'is_payed' => 'boolean',
        'total_price' => 'real',
        'remaining_price' => 'real',
        'payed_price' => 'real',
    ];

    public function billable(): MorphTo
    {
        return $this->morphTo();
    }

    public function list(): MorphTo
    {
        return $this->billable();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    protected static function boot()
    {
        static::creating(function ($model) {
            $model->code = Str::uuid()->toString();
        });
        parent::boot();
    }

    //Attributes:

    public function getRemainingPriceAttribute()
    {
        return +number_format($this->total_price - $this->payed_price, 2, '.', '');
    }

    public function getPayedPriceAttribute()
    {
        if ($this->relationLoaded('payments')) {
            return +$this->payments->sum('price');
        } else {
            return +$this->payments()->sum('price');
        }
    }

    public function getPersonNameAttribute()
    {
        return $this->billable->person->name;
    }

    public function getPersonIdAttribute()
    {
        return $this->billable->person->id;
    }

    public function getPersonDebtsAttribute()
    {
        return $this->billable->person->debts;
    }

    public function getDateAttribute()
    {
        return $this->created_at;
    }

    public function basicAppends()
    {
        $this->append(['payed_price', 'remaining_price', 'person_name', 'date', 'payment_status'])->makeHidden('billable');
        return $this;
    }

    //Methods

    public function getTypeTitleAttribute()
    {
        return match ($this->billable_type) {
            PurchaseList::class => 'Purchase Invoice',
            SaleList::class => 'Sale Invoice',
        };
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->is_payed === true) return "Payed";
        if ($this->payed_price === 0) return "Not Payed";
        return "Partially Payed";
    }

    public function getPaymentStatusColorAttribute()
    {
        if ($this->is_payed === true) return "green";
        if ($this->payed_price === 0) return "red";
        return "orange";
    }

    public function pay($price, $notes = ''): Payment
    {
        if ($price < 0) {
            throw ValidationException::withMessages([__('Payment must not be less than zero')]);
        }

        $remainingPrice = $this->remaining_price;
        if ($price > $remainingPrice && ((string)$price !== (string)$remainingPrice)) {
            throw ValidationException::withMessages([__('Payed price more than bill remaining price')]);
        }

        $payment = $this->payments()->create([
            'price' => $price,
            'currency' => $this->currency,
            'currency_value' => GlobalOption::GetCurrencyValue(),
            'notes' => $notes,
            'payed_at' => now(),
            'received_by' => auth()->id(),
        ]);

        if ((string)$this->payed_price === (string)$this->total_price) {
            $this->is_payed = true;
            $this->save();
        }

        return $payment;
    }
}
