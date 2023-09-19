<?php
declare(strict_types=1);

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperOffWeekDay
 */
class OffWeekDay extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'day' => 'integer',
        'consider_as_attendance' => 'boolean',
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\OffWeekDayFactory::new();
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function salaries(): BelongsToMany
    {
        return $this->belongsToMany(Salary::class)->withPivot(['days']);
    }
}
