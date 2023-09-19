<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Ramsey\Uuid\Uuid;

/**
 * @mixin IdeHelperCategory
 */
class Category extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, CreatedByTrait;

    protected $guarded = [];
    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
