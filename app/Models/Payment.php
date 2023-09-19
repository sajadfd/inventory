<?php

namespace App\Models;

use App\Http\Resources\PaymentResource;
use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperPayment
 */
class Payment extends Model implements Auditable
{
    use  CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];
    protected $casts = [
        'price' => 'real',
        'currency_value' => 'real',
        'payed_at' => 'datetime:Y-m-d H:i:s'
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }


    public function getPriceInIqdAttribute()
    {
        return match ($this->currency) {
            'iqd' => $this->price,
            'usd' => $this->price * $this->currency_value,
        };
    }

    public function getPriceInUsdAttribute()
    {
        return match ($this->currency) {
            'iqd' => $this->price / $this->currency_value,
            'usd' => $this->price,
        };
    }

    public function toResource($withRelations = true): PaymentResource
    {
        return new PaymentResource($this, $withRelations);
    }

    protected static function boot()
    {
        static::creating(function ($model) {
            $model->code = Str::uuid()->toString();
        });
        parent::boot();
    }

}
