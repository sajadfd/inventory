<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperDiagnosis
 */
class Diagnosis extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function saleLists()
    {
        return $this->hasMany(SaleList::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
