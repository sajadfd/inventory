<?php

namespace App\Models;

use App\Contracts\ProductItemInterface;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperOrderItem
 */
class OrderItem extends Model implements Auditable, ProductItemInterface
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $casts = [
        'count' => 'real',
        'price' => 'real',
        'total_price' => 'real',
        'currency_value' => 'real',
    ];

    protected $guarded = [];

    public function transactions(): MorphMany
    {
        return $this->morphMany(ProductTransaction::class, 'targetable');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class,);
    }

    public function productUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->total_price = $model->count * $model->price;
        });
    }

    public function list(): BelongsTo
    {
        return $this->order();
    }
}
