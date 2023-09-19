<?php
declare(strict_types=1);

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\Penalty;
use Modules\HR\Entities\Salary;
use Modules\HR\Transformers\SalaryDataObject;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

uses(RefreshDatabase::class, TestCase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
});

test('penalties does not affect salaries outside its date', function () {
    $contract = Contract::factory()->sample1()->create();
    Penalty::factory(3)->create(['price' => 500000, 'date' => '2023-05-01']);
    Carbon::setTestNow('2023-04-01');
    $contract->calculateDues()->each(fn(SalaryDataObject $salaryDataObject) => $salaryDataObject->store());
    $salaries = Salary::withCount('penalties')->get();
    expect(+$salaries->sum('penalties_count'))->toBe(0);
});

test('penalties  affect salaries outside its date', function () {
    $contract = Contract::factory()->sample1()->create();
    Penalty::factory(3)->create(['price' => 500000, 'date' => '2023-05-01 10:00:00']);
    $penaltiesDivisions = 7;
    Carbon::setTestNow('2024-12-01');
    $contract->calculateDues()->each(fn(SalaryDataObject $salaryDataObject) => $salaryDataObject->store());
    $salaries = Salary::withCount('penalties')->get();
    expect(+$salaries->sum('penalties_count'))->toBe($penaltiesDivisions)
        ->and(+Penalty::all()->sum('remaining_price'))->toBe(0.0)
        ->and(Salary::count())->toBe(12);
});

test('penalties index works', function () {
    Contract::factory(10)->create();
    Penalty::factory(10)->create();
    $res = get('api/penalties?filter[contract.employer.name]=Ahmed&filter[contract_id]=2&filter[contract.employer.id]=2&filter[start_date]=2020-01-01&filter[end_date]=2024-01-01');
    $res->assertStatus(200);
});
test('penalties show work', function () {
    get('api/penalties/' . Penalty::factory()->createOne()->id)->assertStatus(200)->assertJsonPath('data.salaries', []);
});

test('penalties store work', function () {
    $contract = Contract::factory()->sample1()->createOne();
    $res = post('api/penalties', [
        'contract_id' => $contract->id,
        'date' => fake()->dateTimeBetween($contract->start_date, $contract->end_date)->format('Y-m-d H:i:s'),
        'price' => 10000,
        'notes' => 'bla'
    ]);
    $res->assertStatus(200);
    expect($contract->penalties()->count())->toBe(1);
});
test('penalties update work', function () {
    $penalty = Penalty::factory()->createOne();

    $res = put('api/penalties/' . $penalty->id, [
        'contract_id' => $penalty->contract->id,
        'date' => $penalty->date->addDay(),
        'price' => 50,
        'notes' => 'bla'
    ]);
    $res->assertStatus(200);
    $penalty->refresh();
    expect(Penalty::count())->toBe(1)
        ->and(+$penalty->price)->toBe(50.0);
});

test('penalties delete work', function () {
    $penalty = Penalty::factory()->createOne();
    delete('api/penalties/' . $penalty->id)->assertStatus(200);
    expect(Penalty::count())->toBe(0);
});
