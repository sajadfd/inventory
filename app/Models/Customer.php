<?php

namespace App\Models;

use App\Traits\BillableAttributesTrait;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperCustomer
 */
class Customer extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable, BillableAttributesTrait;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function saleLists()
    {
        return $this->hasMany(SaleList::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function bills()
    {
        return $this->hasManyThrough(Bill::class, SaleList::class, 'customer_id', 'billable_id')
            ->where('billable_type', SaleList::class)
            ->with('payments');
    }


    public function getDebtInUsdAttribute()
    {
        return $this->bills->sum('remaining_price');
    }

}
