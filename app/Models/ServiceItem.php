<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperServiceItem
 */
class ServiceItem extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $with = ['service'];

    protected $guarded = [];

    protected $casts = [
        'count' => 'integer',
        'price' => 'real',
        'total_price' => 'real',
        'currency_value' => 'real',
    ];

    public function saleList()
    {
        return $this->belongsTo(SaleList::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->total_price = $model->count * $model->price;
        });
    }


}
