<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperCartItem
 */
class CartItem extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, CreatedByTrait;

    protected $guarded = [];

    protected $casts = [
        'count' => 'real'
    ];

    protected $appends = ['total_price', 'currency', 'currency_value'];

    //Relations
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    //Attributes
    public function getTotalPriceAttribute()
    {
        return $this->count * $this->product->sale_price_in_iqd;
    }

    public function getCurrencyAttribute()
    {
        return 'iqd';
    }

    public function getCurrencyValueAttribute()
    {
        return GlobalOption::GetCurrencyValue();
    }
}
