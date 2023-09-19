<?php

namespace App\Models;

use App\Enums\ProductTransactionEnum;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperProductTransaction
 */
class ProductTransaction extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'count' => 'real',
        'type' => ProductTransactionEnum::class,
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function targetable(): MorphTo
    {
        return $this->morphTo();
    }

    public function sourceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function sourceProductUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'source_product_unit_id', 'id');
    }

    public function targetProductUnit(): BelongsTo
    {
        return $this->belongsTo(ProductUnit::class, 'target_product_unit_id', 'id');
    }
}
