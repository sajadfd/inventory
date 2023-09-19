<?php

namespace App\Models;

use App\Contracts\ProductListInterface;
use App\Enums\SaleType;
use App\Http\Resources\SaleListResource;
use App\Services\ProductStoreService;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use OwenIt\Auditing\Contracts\Auditable;


/**
 * @mixin IdeHelperSaleList
 */
class SaleList extends Model implements Auditable, ProductListInterface
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'is_confirmed' => 'boolean',
        'date' => 'datetime:Y-m-d H:i:s',
        'type' => SaleType::class,
    ];

    public function person(): BelongsTo
    {
        return $this->customer();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class);
    }

    public function mechanic()
    {
        return $this->belongsTo(Mechanic::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class)->with('product');
    }

    public function items(): HasMany
    {
        return $this->saleItems();
    }

    public function initialSaleItems()
    {
        return $this->saleItems()->orderBy('id')->limit(20);
    }

    public function serviceItems()
    {
        return $this->hasMany(ServiceItem::class);
    }

    public function bill()
    {
        return $this->morphOne(Bill::class, 'billable');
    }

    public function billPayments()
    {
        return $this->hasManyThrough(Payment::class, Bill::class, 'billable_id')
            ->where('billable_type', SaleList::class);
    }

    public function loadBasicAttributes()
    {
        $this->loadMissing(['customer', 'bill', 'saleItems', 'serviceItems']);

        $this->append([
            'sale_items_total_price',
            'sale_items_total_pieces',
            'sale_items_count',
            'service_items_total_price',
            'service_items_total_pieces',
            'service_items_count',
            'total_price'
        ]);
    }

    //Attributes

    public function getSaleItemsTotalPiecesAttribute()
    {
        return $this->saleItems->sum('count');
    }

    public function getSaleItemsTotalPriceAttribute()
    {
        return $this->saleItems->sum('total_price');
    }

    public function getSaleItemsCountAttribute()
    {
        return $this->saleItems->count();
    }

    public function getServiceItemsTotalPiecesAttribute()
    {
        return $this->serviceItems->sum('count');
    }

    public function getServiceItemsTotalPriceAttribute()
    {
        return $this->serviceItems->sum('total_price');
    }

    public function getServiceItemsCountAttribute()
    {
        return $this->serviceItems->count();
    }

    public function getTotalPriceAttribute()
    {
        return $this->service_items_total_price + $this->sale_items_total_price;
    }

    public function getInitialSaleItemsAttribute()
    {
        return $this->saleItems->take(20);
    }

    //Methods
    public function toResource(): SaleListResource
    {
        return new SaleListResource($this);
    }

    /**
     * @throws \Throwable
     */
    public function confirm($autoPay = false):self
    {
        DB::transaction(function () use ($autoPay) {
            if ($this->is_confirmed) {
                throw ValidationException::withMessages([__('List is already confirmed')]);
            } else if ($this->bill()->exists()) {
                throw new \InvalidArgumentException(__("List already has a bill"));
            }

            $bill = $this->bill()->create([
                'total_price' => $this->total_price,
                'currency' => $this->currency,
            ]);

            if ($autoPay) {
                $bill->pay($this->total_price);
            }

            $this->update(['is_confirmed' => true]);
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
            $this->bill?->payments()->delete();
            $this->bill?->delete();

            $this->update(['is_confirmed' => false]);
        });
        return $this;
    }

}
