<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperPurchaseItem
 */
class PurchaseItem extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'count' => 'real',
        'free_count' => 'real',
        'back_count' => 'real',
        'net_count' => 'real',
        'in_stock_count' => 'real',
        'used' => 'real',
        'price' => 'real',
        'total_price' => 'real',
        'currency_value' => 'real',
    ];

    public function purchaseList(): BelongsTo
    {
        return $this->belongsTo(PurchaseList::class);
    }

    public function list(): BelongsTo
    {
        return $this->purchaseList();
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function usageTransactions()
    {
        return $this->morphMany(ProductTransaction::class, 'sourceable');
    }

    public function purchaseTransaction()
    {
        return $this->morphOne(ProductTransaction::class, 'targetable');
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    //Attribute
    public function getNetCountAttribute()
    {
        return $this->count + $this->free_count - $this->back_count;
    }

    public function getPriceInIqdAttribute()
    {
        return match ($this->currency) {
            'iqd' => $this->price,
            'usd' => $this->price * $this->currency_value,
        };
    }

    public function getPriceInUsdAttribute()
    {
        return match ($this->currency) {
            'iqd' => $this->price / $this->currency_value,
            'usd' => $this->price,
        };
    }

    public function getTotalPriceInIqdAttribute()
    {
        return match ($this->currency) {
            'iqd' => $this->total_price,
            'usd' => $this->total_price * $this->currency_value,
        };
    }

    public function getTotalPriceInUsdAttribute()
    {
        return match ($this->currency) {
            'iqd' => $this->total_price / $this->currency_value,
            'usd' => $this->total_price,
        };
    }

    public function getInStockCountAttribute()
    {
        return $this->net_count - $this->used;
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->total_price = $model->net_count * $model->price;
        });
    }


}// /PurchaseItem
