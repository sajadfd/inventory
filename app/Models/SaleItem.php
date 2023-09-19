<?php

namespace App\Models;

use App\Contracts\ProductItemInterface;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperSaleItem
 */
class SaleItem extends Model implements Auditable, ProductItemInterface
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'count' => 'real',
        'free_count' => 'real',
        'back_count' => 'real',
        'net_count' => 'real',
        'price' => 'real',
        'total_price' => 'real',
        'currency_value' => 'real',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function saleList()
    {
        return $this->belongsTo(SaleList::class);
    }

    public function list(): BelongsTo
    {
        return $this->saleList();
    }

    public function transactions(): MorphMany
    {
        return $this->morphMany(ProductTransaction::class, 'targetable');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    //Attributes
    public function getNetCountAttribute()
    {
        return $this->count + $this->free_count - $this->back_count;
    }

    public function getPriceInUsdAttribute()
    {
        return match ($this->currency) {
            'usd' => $this->price,
            'iqd' => $this->price / $this->currency_value
        };
    }

    public function getTotalPriceInUsdAttribute()
    {
        return match ($this->currency) {
            'usd' => $this->total_price,
            'iqd' => $this->total_price / $this->currency_value
        };
    }

    public function getTotalPriceInIqdAttribute()
    {
        return match ($this->currency) {
            'usd' => $this->total_price * $this->currency_value,
            'iqd' => $this->total_price
        };
    }

    public function getPurchasePriceInIqdAttribute()
    {
        return $this->transactions->sum(fn($transaction) => $transaction->sourceable->price_in_iqd * $transaction->count);
    }

    public function getPurchasePriceInUsdAttribute()
    {
        return $this->transactions->sum(fn($transaction) => $transaction->sourceable->price_in_usd * $transaction->count);
    }

    public function getEarnPriceInIqdAttribute()
    {
        return $this->total_price_in_iqd - $this->purchase_price_in_iqd;
    }

    public function getEarnPriceInUsdAttribute()
    {
        return $this->total_price_in_usd - $this->purchase_price_in_usd;
    }
 

    //Boot

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->total_price = $model->net_count * $model->price;
        });
    }

}
