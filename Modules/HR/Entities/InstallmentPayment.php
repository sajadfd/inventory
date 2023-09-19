<?php

namespace Modules\HR\Entities;

use App\Enums\CurrencyEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Support\Str;

/**
 * @mixin IdeHelperInstallmentPayment
 */
class InstallmentPayment extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'price' => 'real',
        'currency_value' => 'real',
        'currency' => CurrencyEnum::class,
        'payed_at' => 'datetime:Y-m-d H:i:s',
    ];

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    protected static function boot()
    {
        static::creating(function ($model) {
            $model->code = Str::uuid()->toString();
        });
        parent::boot();
    }
}
