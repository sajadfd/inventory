<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\HR\Database\factories\PenaltyFactory;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @method PenaltyFactory factory(int $count=1)
 * @mixin IdeHelperPenalty
 */
class Penalty extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    public $localPayedPrice = null; // Used to temporarily modifying paid is_paid price

    protected $guarded = [];

    protected $casts = [
        'price' => 'real',
        'currency_value' => 'real',
        'date' => 'datetime:Y-m-d H:i:s',
    ];

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\PenaltyFactory::new();
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function salaries(): BelongsToMany
    {
        return $this->belongsToMany(Salary::class)->withPivot(['price']);
    }

    public function installment(): HasOne
    {
        return $this->hasOne(Installment::class);
    }

    public function getPayedPriceAttribute()
    {
        return $this->localPayedPrice !== null ? $this->localPayedPrice : (
        $this->relationLoaded('salaries') ? +$this->salaries->sum('pivot.price') :
            +$this->salaries()->sum('penalty_salary.price')
        );
    }

    public function getRemainingPriceAttribute()
    {
        return $this->price - $this->payed_price;
    }

}
