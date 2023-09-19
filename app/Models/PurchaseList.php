<?php

namespace App\Models;

use App\Contracts\ProductListInterface;
use App\Services\ProductStoreService;
use App\Traits\CreatedByTrait;
use Database\Factories\PurchaseListFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @method PurchaseListFactory factory(int $count = 0)
 * @mixin IdeHelperPurchaseList
 */
class PurchaseList extends Model implements Auditable, ProductListInterface
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'date' => 'datetime:Y-m-d H:i:s',
    ];

    public function bill(): MorphOne
    {
        return $this->morphOne(Bill::class, 'billable');
    }

    public function billPayments()
    {
        return $this->hasManyThrough(Payment::class, Bill::class, 'billable_id')
            ->where('billable_type', PurchaseList::class);
    }

    public function loadBasicAttributes()
    {
        $this->loadMissing(['supplier', 'bill', 'purchaseItems']);
        $this->append([
            'total_price',
            'total_pieces',
        ]);
    }

    public function purchaseItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PurchaseItem::class, 'purchase_list_id')->with('product');
    }

    public function items(): HasMany
    {
        return $this->purchaseItems();
    }

    public function person(): BelongsTo
    {
        return $this->supplier();
    }

    public function supplier(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }


    //Attributes
    public function getInitialPurchaseItemsAttribute()
    {
        return $this->purchaseItems->take(20);
    }

    public function getTotalPriceAttribute()
    {
        return $this->purchaseItems->sum('total_price');
    }

    public function getTotalPiecesAttribute()
    {
        return $this->purchaseItems->sum('count');
    }

    public function getTotalItemsAttribute()
    {
        return $this->purchaseItems->count();
    }

    public function getPayedPriceAttribute()
    {
        return $this->billPayments->sum('price');
    }

    //Methods

    /**
     * @throws \Throwable
     */
    public function confirm($autoPay = false): static
    {

        DB::transaction(function () use ($autoPay) {
            if ($this->is_confirmed) {
                throw ValidationException::withMessages([__('List is already confirmed')]);
            } else if ($this->bill()->exists()) {
                throw new \InvalidArgumentException(__("List already has a bill"));
            }

            $this->purchaseItems()->each(function (PurchaseItem $purchaseItem) {
                ProductStoreService::UpdateStoreFromPurchase($purchaseItem->product, $purchaseItem,$purchaseItem->productUnit);
            });

            $bill = $this->bill()->create([
                'total_price' => round($this->total_price, 2),
                'currency' => $this->currency,
            ]);

            if ($autoPay) {
                $bill->pay(round($this->total_price, 2));
            }

            $this->is_confirmed = true;
            $this->save();
        });

        return $this;
    }


    /**
     * @throws \Throwable
     */
    public function unConfirm(): self
    {
        DB::transaction(function () {
            if (!$this->is_confirmed) {
                throw ValidationException::withMessages([__('List is not confirmed')]);
            }

            $purchaseItems = $this->purchaseItems->load(['usageTransactions', 'purchaseTransaction', 'product', 'product.productUnits', 'product.productLocation', 'product.category', 'product.brand','product.initialStore']);
            if ($purchaseItems->firstWhere(fn(PurchaseItem $purchaseItem) => $purchaseItem->usageTransactions->isNotEmpty())) {
                throw ValidationException::withMessages([__('List items has uses,cannot un confirm')]);
            }

            $this->purchaseItems->each(function (PurchaseItem $purchaseItem) {
                if (!ProductStoreService::RefundPurchaseFromStore($purchaseItem->product, $purchaseItem)) {
                    throw ValidationException::withMessages([__('Error Occurred')]);
                };
            });

            $this->bill?->payments()->delete();
            $this->bill?->delete();

            $this->update(['is_confirmed' => false]);
        });
        return $this;
    }

}
