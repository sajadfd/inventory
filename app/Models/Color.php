<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperColor
 */
class Color extends Model implements Auditable
{
    use HasFactory, CreatedByTrait,\OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts=[
        'is_active'=>'boolean',
    ];

    public function cars()
    {
        return $this->hasMany(Car::class);
    }
}
