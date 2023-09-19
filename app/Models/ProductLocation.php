<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Database\Factories\ProductLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @method ProductLocationFactory factory(int $count = 1)
 * @mixin IdeHelperProductLocation
 */
class ProductLocation extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = ['is_active' => 'boolean'];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
