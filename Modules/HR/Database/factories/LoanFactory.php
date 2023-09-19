<?php

namespace Modules\HR\Database\factories;

use App\Enums\CurrencyEnum;
use Carbon\Carbon;
use Modules\HR\Enums\PaymentMethodEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\InstallmentPayment;
use Modules\HR\Entities\Loan;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition()
    {
        return [
            "contract_id" => (Contract::query()->inRandomOrder()->first() ?: ContractFactory::new()->createOne())->id,
            'price' => fake()->randomFloat(2, 1000, 10000),
            'profit_percent' => fake()->randomFloat(2, 0, 50),
            'currency' => fake()->randomElement(CurrencyEnum::getAllValues()),
            'currency_value' => fake()->randomFloat(2, 1, 10),
            'duration' => fake()->numberBetween(1, 100),
            'payment_method' => fake()->randomElement(PaymentMethodEnum::getAllValues()),
            'date' => fake()->dateTimeThisMonth(),
        ];
    }
    public function configure()
    {
        return $this->afterCreating(function (Loan $loan) {

            (new Loan)->createInstallments($loan);
            $installments = $loan->installments()->where('loan_id', $loan->id)->get();
            if ($installments) {
                foreach ($installments as $installment) {
                    $dueDate = $installment->due_date;
                    $payedAt = Carbon::parse($dueDate)->subMonth()->format('Y-m-d');
                    $data = [
                        "installment_id" => $installment->id,
                        'price' => fake()->randomFloat(2, 1, $installment->price),
                        'currency' =>   $installment->loan->currency,
                        'currency_value' => $installment->loan->currency_value,
                        'payed_at' => $payedAt,
                    ];
                    InstallmentPayment::query()->create($data);
                }
            }
        });
    }
}
