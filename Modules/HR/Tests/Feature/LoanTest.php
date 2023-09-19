<?php

declare(strict_types=1);

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\Seeders\LoanSeeder;
use Modules\HR\Entities\Contract;
use Modules\HR\Enums\PaymentMethodEnum;
use Modules\HR\Enums\SalaryCurrencyEnum;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Modules\HR\Entities\InstallmentPayment;
use Modules\HR\Entities\Loan;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class, TestCase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
    Date::setTestNow('2023-08-24 10:00:00');
});

test('loan index works', function () {
    seed(LoanSeeder::class);
    $res=get('api/loans?exclude=contract,installments.*.loan&include=installments.loan');
    dump($res->json());
})->only();

test('loan can successfully created', function () {
    $contract = Contract::factory()->sample1()->createOne();

    $response = post('api/loans', [
        'contract_id' => $contract->id,
        'price' => 5000000,
        'profit_percent' => 10,
        'currency' => 'iqd',
        'currency_value' => 100.50,
        'duration' => 5,
        'payment_method' => 'monthly',
        'date' => '2023-08-26 12:00:00',
    ]);
    $loan = Loan::query()->latest()->first();
    if ($loan) {
        (new Loan)->createInstallments($loan);
        $installments = $loan->installments()->where('loan_id', $loan->id)->get();
        if ($installments) {
            foreach ($installments as $installment) {
                $dueDate = $installment->due_date;
                $payedAt = Carbon::parse($dueDate)->subMonth()->format('Y-m-d');
                $data = [
                    "installment_id" => $installment->id,
                    'price' => fake()->randomFloat(2, 1, $installment->price),
                    'currency' => $installment->loan->currency,
                    'currency_value' => $installment->loan->currency_value,
                    'payed_at' => $payedAt,
                ];
                InstallmentPayment::query()->create($data);
            }
        }
    }

    $response->assertStatus(200);
});

test('loan can successfuly get by ID', function () {
    $loan = Loan::factory()->createOne();

    $response = get("api/loans/{$loan->id}");

    $response->assertStatus(200);
});

test('loan can successfuly updated', function () {
    $contract = Contract::factory()->sample1()->createOne();

    $loan = Loan::factory()->createOne();

    $response = put("api/loans/{$loan->id}", [
        'contract_id' => $contract->id,
        'price' => 1500,
        'profit_percent' => 10,
        'currency' => SalaryCurrencyEnum::Usd->value,
        'currency_value' => 1.00,
        'duration' => 12,
        'payment_method' => PaymentMethodEnum::Daily->value,
        'date' => '2023-06-01 12:00:00'
    ]);

    $response->assertStatus(200);
});

test('loan can successfuly deleted', function () {
    $loan = Loan::factory()->createOne();

    $response = delete("api/loans/{$loan->id}");

    $response->assertStatus(200);
});
