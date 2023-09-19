<?php

namespace Modules\HR\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @mixin IdeHelperLoan
 */
class Loan extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $guarded = [];

    protected $casts = [
        'price' => 'real',
        'currency_value' => 'real',
        'date' => 'datetime:Y-m-d H:i:s',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class);
    }

    protected static function newFactory()
    {
        return \Modules\HR\Database\factories\LoanFactory::new();
    }

    public function createInstallments($loan)
    {
        $loanDate = $loan->date;
        $originalPrice = floatval($loan->price);
        $profitPercent = floatval($loan->profit_percent);
        $totalPrice = $originalPrice + ($originalPrice * ($profitPercent / 100));
        $duration = floatval($loan->duration);
        $installmentPrice = $totalPrice / $duration;

        foreach (range(1, $duration) as $sequenceNumber) {
            $dueDate = $loanDate->addMonth();
            $data = [
                'loan_id' => $loan->id,
                'price' => $installmentPrice,
                'due_date' => $dueDate,
                'sequence_number' => $sequenceNumber,
            ];
            Installment::create($data);
        }
        // return Installment::get();
    }
}
