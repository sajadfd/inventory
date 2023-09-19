<?php
declare(strict_types=1);

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperMechanic
 */
class Mechanic extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        "name"
    ];

    public function saleLists()
    {
        return $this->hasMany(SaleList::class);
    }
}
