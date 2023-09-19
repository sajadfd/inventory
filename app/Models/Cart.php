<?php

namespace App\Models;

use App\Enums\CartTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Enums\SaleType;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperCart
 */
class Cart extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, CreatedByTrait;

    protected $guarded = [];

    protected $with = ['cartItems', 'cartItems.product'];

    protected $casts = [
        'type' => CartTypeEnum::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class);
    }

    //Attributes
    protected $appends = ['total_price', 'currency'];

    public function getTotalPriceAttribute()
    {
        return $this->cartItems->sum(function (CartItem $cartItem) {
            return $cartItem->count * $cartItem->product->sale_price_in_iqd;
        });
    }

    public function getCurrencyAttribute()
    {
        return 'iqd';
    }

}
