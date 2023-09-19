<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\HR\Database\factories\OffDateFactory;
use Modules\HR\Traits\ContractDateTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @method OffDateFactory factory(int $count=1)
 * @mixin IdeHelperOffDate
 */
class OffDate extends Model implements Auditable
{
    use HasFactory,ContractDateTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'start_date' => 'datetime:Y-m-d H:i:s',
        'end_date' => 'datetime:Y-m-d H:i:s',
        'consider_as_attendance' => 'boolean',
    ];

    protected static function newFactory(): OffDateFactory
    {
        return OffDateFactory::new();
    }

    public function contract():BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function salaries():BelongsToMany
    {
        return $this->belongsToMany(Salary::class)->withPivot(['hours']);
    }

}
