<?php

namespace App\Models;

use App\Traits\BillableAttributesTrait;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperSupplier
 */
class Supplier extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable, BillableAttributesTrait;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function purchaseLists()
    {
        return $this->hasMany(PurchaseList::class);
    }

    public function bills()
    {
        return $this->hasManyThrough(Bill::class, PurchaseList::class, 'supplier_id', 'billable_id')
            ->where('billable_type', PurchaseList::class)
            ->with('payments');
    }


    public function getPurchaseListsTotalItemsAttribute()
    {
        return $this->purchaseLists->sum->total_items;
    }

    public function getPurchaseListsTotalPiecesAttribute()
    {
        return $this->purchaseLists->sum->total_pieces;
    }

    public function getPurchaseListsTotalPriceAttribute()
    {
        return $this->purchaseLists->sum->total_price;
    }

    public function getPurchaseListsPayedPriceAttribute()
    {
        return $this->purchaseLists->sum->payed_price;
    }


}
