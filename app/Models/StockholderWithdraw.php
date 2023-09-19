<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockholderWithdraw extends Model
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'price' => 'real',
        'currency_value' => 'real',
        'currency' => CurrencyEnum::class,
        'date' => 'datetime:Y-m-d H:i:s',
    ];
}
