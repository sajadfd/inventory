<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @method decrement($column, $amount = 1, array $extra = [])
 * @method increment($column, $amount = 1, array $extra = [])
 * @method ProductFactory factory(int $count = 1)
 * @mixin IdeHelperProduct
 */
class Product extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

//    protected $appends=['calculated_store'];
    protected $appends = ['default_unit'];
    protected $guarded = [];

    protected $with = ['category', 'brand', 'productLocation', 'productUnits', 'initialStore'];

    protected $casts = [
        'sale_price' => 'real',
        'calculated_store' => 'real',
        'store' => 'real',
        'depletion_alert_at' => 'integer',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_visible_in_store' => 'boolean',
    ];

    //Relations:
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }


    public function initialStore()
    {
        return $this->hasOne(InitialStore::class);
    }

    public function inStockPurchaseItems()
    {
        return $this->hasMany(PurchaseItem::class)
            ->whereHas('purchaseList', function ($query) {
                return $query->where('is_confirmed', true);
            })
            ->orderBy('created_at')->whereColumn('used', '<', 'count');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function productLocation(): BelongsTo
    {
        return $this->belongsTo(ProductLocation::class);
    }

    public function productUnits(): HasMany
    {
        return $this->hasMany(ProductUnit::class);
    }

    public function getDefaultUnitAttribute(): ProductUnit|Model|null
    {
        return $this->relationLoaded('productUnits') ? $this->productUnits->where('is_default', true)->first()
            : $this->productUnits()->where('is_default', true)->first();
    }

    public function transactions()
    {
        return $this->hasMany(ProductTransaction::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function confirmedPurchaseItems()
    {
        return $this->hasMany(PurchaseItem::class)->whereHas('purchaseList', function ($query) {
            return $query->where('is_confirmed', true);
        });
    }

    public function latestPurchaseItem()
    {
        return $this->hasOne(PurchaseItem::class)->orderByDesc('created_at');
    }

    //Scopes:

    public function scopeBasicRelations(Builder $query): void
    {
        $query->with('latestPurchaseItem');
    }


    //Attributes:

    public function getSalePriceInIqdAttribute()
    {
        $price = match ($this->sale_currency) {
            'iqd' => $this->sale_price,
            'usd' => $this->sale_price * GlobalOption::GetCurrencyValue(),
        };

        if (GlobalOption::GetIqdSaleToNearestPayablePrice()) {
            $price = ceil($price / 250) * 250;
        }

        return $price;
    }

    public function getSalePriceInUsdAttribute()
    {
        return match ($this->sale_currency) {
            'iqd' => $this->sale_price / GlobalOption::GetCurrencyValue(),
            'usd' => $this->sale_price,
        };
    }

    public function getLatestPurchasePriceAttribute()
    {
        return $this->latestPurchaseItem?->price ?: $this->initialStore?->price;
    }

    public function getLatestPurchaseCurrencyAttribute()
    {
        return $this->latestPurchaseItem?->currency ?: $this->initialStore?->currency ?: 'usd';
    }

    public function getLatestPurchaseCurrencyValueAttribute()
    {
        return $this->latestPurchaseItem?->currency_value ?: $this->initialStore?->currency_value ?: GlobalOption::GetCurrencyValue();
    }

    public function getLatestPurchasePriceInIqdAttribute()
    {
        return match ($this->latest_purchase_currency) {
            'iqd' => $this->latest_purchase_price,
            'usd' => $this->latest_purchase_price * $this->latest_purchase_currency_value,
        };
    }

    public function getLatestPurchasePriceInUsdAttribute()
    {
        return match ($this->latest_purchase_currency) {
            'iqd' => $this->latest_purchase_price / $this->latest_purchase_currency_value,
            'usd' => $this->latest_purchase_price,
        };
    }

    public function getCalculatedStoreAttribute(): int
    {
        return $this->initialStore?->in_stock_count + $this->confirmedPurchaseItems->sum('in_stock_count');
    }

    //Methods

    public function salePriceIn($currency = 'iqd', $currencyValue = 0)
    {
        if ($currencyValue <= 0) $currencyValue = GlobalOption::GetCurrencyValue();

        return match ($currency) {
            'iqd' => match ($this->sale_currency) {
                'iqd' => $this->sale_price,
                'usd' => $this->sale_price * $currencyValue,
            },
            'usd' => match ($this->sale_currency) {
                'iqd' => $this->sale_price / $currencyValue,
                'usd' => $this->sale_price,
            },
        };
    }


}
