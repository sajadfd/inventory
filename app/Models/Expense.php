<?php

namespace App\Models;

use App\Traits\CreatedByTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ExpenseSource;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperExpense
 */
class Expense extends Model implements Auditable
{
    use HasFactory, CreatedByTrait, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'source' => ExpenseSource::class,
        'price' => 'real',
        'date' => 'datetime:Y-m-d H:i:s',
        'currency_value' => 'real',
    ];

    public function getPriceInIqdAttribute()
    {
        return match ($this->currency){
          'iqd'=>$this->price,
          'usd'=>$this->price * $this->currency_value
        };
    }
}
