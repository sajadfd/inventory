<?php

namespace App\Models;

use App\Enums\ProductUnitType;
use App\Services\ProductStoreService;
use App\Traits\CreatedByTrait;
use Database\Factories\ProductUnitFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @method ProductUnitFactory factory(int $count = null, array $state=null)
 * @property float $store
 * @mixin IdeHelperProductUnit
 */
class ProductUnit extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'factor' => 'real',
        'price' => 'real',
        'store' => 'real',
        'type' => ProductUnitType::class,
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'is_visible_in_store' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function sourceTransactions(): HasMany
    {
        return $this->hasMany(ProductTransaction::class, 'source_product_unit_id');
    }

    public function targetTransactions(): HasMany
    {
        return $this->hasMany(ProductTransaction::class, 'target_product_unit_id');
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function getPriceInIqdAttribute(): float
    {
        return match ($this->currency) {
            'iqd' => $this->price,
            'usd' => $this->price * GlobalOption::GetCurrencyValue(),
        };
    }

    public function getPriceInUsdAttribute(): float
    {
        return match ($this->currency) {
            'iqd' => $this->price / GlobalOption::GetCurrencyValue(),
            'usd' => $this->price,
        };
    }

    public function store(): Attribute
    {
        return Attribute::make(
            get: fn() => ProductStoreService::convertCountToUnit($this->product->store, null, $this)
        )->withoutObjectCaching();
    }
}
