<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @method decrement($column, $amount = 1, array $extra = [])
 * @method increment($column, $amount = 1, array $extra = [])
 * @mixin IdeHelperInitialStore
 */
class InitialStore extends Model implements Auditable
{
    use CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'count' => 'real',
        'used' => 'real',
        'in_stock_count' => 'real',
        'price' => 'real',
        'currency_value' => 'real',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function list()
    {
        return new BelongsTo($this->newQuery(), $this, '', '', '');
    }

    public function usageTransactions()
    {
        return $this->morphMany(ProductTransaction::class, 'sourceable');
    }

    public function initialTransaction()
    {
        return $this->morphOne(ProductTransaction::class, 'targetable');
    }

    public function getInStockCountAttribute()
    {
        return $this->count - $this->used;
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


}
