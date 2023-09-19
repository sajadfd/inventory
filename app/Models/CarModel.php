<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperCarModel
 */
class CarModel extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable,CreatedByTrait;

    protected $guarded = [];

    protected $casts=[
        'is_active'=>'boolean',
    ];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }
}
