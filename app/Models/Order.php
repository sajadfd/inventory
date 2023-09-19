<?php

namespace App\Models;

use App\Contracts\ProductListInterface;
use App\Enums\OrderStatusEnum;
use App\Enums\SaleType;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperOrder
 */
class Order extends Model implements Auditable, ProductListInterface
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $with = ['driver', 'customer', 'diagnosis', 'car', 'orderItems', 'orderItems.product', 'orderItems.product.category', 'orderItems.product.brand', 'orderItems.product.initialStore', 'saleList',];

    protected $appends = ['total_price'];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'type' => SaleType::class,
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public function person(): BelongsTo
    {
        return $this->customer();
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function items(): HasMany
    {
        return $this->orderItems();
    }

    public function saleList()
    {
        return $this->hasOne(SaleList::class);
    }

    public function diagnosis(): BelongsTo
    {
        return $this->belongsTo(Diagnosis::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    //Attributes

    public function getTotalPriceAttribute()
    {
        return $this->orderItems->sum('total_price');
    }

    //Methods

    public function confirm()
    {
        $order = $this;
        $saleList = SaleList::create([
            'customer_id' => $order->customer_id,
            'type' => $order->type,
            'notes' => $order->notes,
            'order_id' => $order->id,
            'currency' => $order->currency,
            'date' => now(),
        ]);

        $order->orderItems->each(function (OrderItem $orderItem) use ($saleList) {
            $saleItem = $saleList->saleItems()->create([
                'count' => $orderItem->count,
                'product_id' => $orderItem->product_id,
                'product_unit_id' => $orderItem->product_unit_id,
                'sale_list_id' => $saleList->id,
                'price' => $orderItem->price,
                'currency' => $orderItem->currency,
                'currency_value' => $orderItem->currency_value,
            ]);
            $orderItem->transactions()->update([
                'targetable_id' => $saleItem->id,
                'targetable_type' => SaleItem::class,
            ]);
        });

        if ($saleList->type === SaleType::StoreSale) {
            $saleList->confirm();
        }
        $order->update(['status' => OrderStatusEnum::ConfirmedByAdmin]);
        return $this;
    }
}
