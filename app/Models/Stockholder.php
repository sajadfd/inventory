<?php

namespace App\Models;

use App\Http\Resources\StockholderResource;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperStockholder
 */
class Stockholder extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = ['inventory_stocks' => 'real', 'store_stocks' => 'real'];

    public function toResource()
    {
        return new StockholderResource($this);
    }

    public function getTotalStocksAttribute()
    {
        return $this->inventory_stocks + $this->store_stocks;
    }
    public function StockholderWithdrawals()
    {
        return $this->hasMany(StockholderWithdraw::class);
    }
}
