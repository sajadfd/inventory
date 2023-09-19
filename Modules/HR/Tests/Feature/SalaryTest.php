<?php
declare(strict_types=1);

namespace Modules\HR\Tests\Feature;

use App\Enums\GlobalOptionEnum;
use App\Enums\PermissionEnum;
use App\Models\GlobalOption;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\Seeders\SalarySeeder;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\Employer;
use Modules\HR\Entities\Salary;
use Modules\HR\Enums\HRPermissionEnum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\artisan;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class, TestCase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
    \Date::setTestNow('2023-06-01 10:00:00');
});

test('salary index works', function () {
    seed([SalarySeeder::class]);
    $user = User::factory()->inventoryAdmin()->create();
    Sanctum::actingAs($user);
    get('api/salaries')->assertStatus(403);

    $user->givePermissionTo(HRPermissionEnum::VIEW_SALARIES);

    $res = get('api/salaries');
    $res->assertStatus(200);
    $res->assertJsonPath('data.total', Salary::count());


    $this->app->get('auth')->forgetGuards();

    get('api/salaries')->assertStatus(401);
});

test('salary show works', function () {
    seed([SalarySeeder::class]);
    $salary = Salary::inRandomOrder()->first();
    get('api/salaries/' . $salary->id)->assertStatus(200);
});

test('salary delete works', function () {
    seed([SalarySeeder::class]);
    $paidSalary = Salary::factory()->paidStatus(true)->createOne();
    $unPaidSalary = Salary::factory()->paidStatus(false)->createOne();
    $initialCount = Salary::count();
    delete('api/salaries/' . $paidSalary->id)->assertStatus(422);
    delete('api/salaries/' . $unPaidSalary->id)->assertStatus(200);

    expect(Salary::count())->toBe($initialCount - 1);
});

test('salary can be paid', function () {
    $paidSalary = Salary::factory()->paidStatus(true)->createOne();
    $unPaidSalary = Salary::factory()->paidStatus(false)->createOne();
    post('api/salaries/' . $paidSalary->id . '/pay')->assertStatus(422);
    post('api/salaries/' . $unPaidSalary->id . '/pay', ['notes' => 'notes'])->assertStatus(200);

    $unPaidSalary->refresh();
    expect($unPaidSalary->is_payed)->toBeTrue()
        ->and($unPaidSalary->payed_at)->toBeGreaterThanOrEqual(now()->subMinutes(1))
        ->and($unPaidSalary->notes)->toBe('notes');
});

test('many salaries can be paid ', function () {
    $paidSalary = Salary::factory()->paidStatus(true)->createOne();
    $unPaidSalaries = Salary::factory(5)->paidStatus(false)->create();
    post('api/salaries/pay_many', [$paidSalary->id, $unPaidSalaries->pluck('id')->toArray()])->assertStatus(422);
    post('api/salaries/pay_many', ['notes' => 'notes', 'ids' => $unPaidSalaries->pluck('id')->toArray()])->assertStatus(200);

    $salaries = Salary::findMany($unPaidSalaries->pluck('id'));

    $salaries->each(function (Salary $salary) {
        expect($salary->is_payed)->toBeTrue()
            ->and($salary->payed_at)->toBeGreaterThanOrEqual(now()->subMinutes(1))
            ->and($salary->notes)->toBe('notes');
    });
});


test('salaries can be calculated', function () {
    $this->app->get('auth')->forgetGuards();

    $res = get('api/salaries/calculate');
    $res->assertStatus(200);
});


test('test simple salary can be issued', function () {
    Date::setTestNow('2023-02-02 00:00:00');
    $contract = Contract::factory()->sample1()->createOne(['salary_price' => 310000]);
    artisan('salaries-calculate');

    $salary = Salary::first();
    expect(Salary::count())->toBe(1)
        ->and($salary->worked_days)->toBe(31.0)
        ->and($salary->price)->toBe(310000.0);
});

test('test salary of 2 months can be issued', function () {
    Date::setTestNow('2023-03-02 00:00:00');
    $contract = Contract::factory()->sample1()->createOne(['salary_price' => 310000]);
    artisan('salaries-calculate');

    $salary1 = Salary::all()[0];
    $salary2 = Salary::all()[1];
    expect(Salary::count())->toBe(2)
        ->and($salary1->worked_days)->toBe(31.0)
        ->and($salary1->price)->toBe(310000.0)
        ->and($salary2->worked_days)->toBe(28.0)
        ->and(round($salary2->price, 2))->toBe(310000.0);
});

test('test salary of 2 months can be issued with unified', function () {
    Date::setTestNow('2023-03-02 00:00:00');
    $contract = Contract::factory()->sample1()->createOne(['salary_price' => 310000]);
    GlobalOption::find(GlobalOptionEnum::UnifyUnpaidSalaries)->update(['value' => true]);

    artisan('salaries-calculate');
    $salary = Salary::first();
    expect(Salary::count())->toBe(1)
        ->and($salary->worked_days)->toBe(59.0)
        ->and(round($salary->price, 2))->toBe(620000.0);
});

test('test salary of 6 months can be issued ', function () {
    /** @var Contract $contract */
    $contract = Contract::factory()->sample1()->createOne(['salary_price' => 310000]);
    Date::setTestNow($now = $contract->start_date->clone()->addMonths(6));
    artisan('salaries-calculate');
    expect(Salary::count())->toBe(6)
        ->and(round(+Salary::sum('price'), 2))->toBe(round($contract->salary_price * 6, 2));
});

test('test salary of multiple employers works months', function () {
    Date::setTestNow('2023-10-05 00:00:00');

    /** @var Contract $contract */
    Employer::factory(5)->create();

    $contractSample1 = Contract::factory(1)->sample1()->createOne(); // 9 Salaries
    Contract::factory(1)->sample1()->create();

    $contractSample2 = Contract::factory()->sample2()->createOne(); // 7 Salaries
    Contract::factory(1)->sample2()->create();
    Contract::factory(1)->sample2()->create();
    artisan('salaries-calculate');
    $contracts2LatestSalaryPrice = Salary::query()->where('contract_id', $contractSample2->id)->orderBy('start_date', 'desc')->first()->price;
    expect(Contract::count())->toBe(5)
        ->and(Employer::count())->toBe(5)
        ->and($contractSample1->salaries()->count())->toBe(9)
        ->and($contractSample2->salaries()->count())->toBe(7)
        ->and(Salary::count())->toBe((9 * 2) + (7 * 3))
        ->and(round(+Salary::sum('price'), 2))->toBe(round((300000 * 2 * 9) + (520000 * 3 * 6) + ($contracts2LatestSalaryPrice * 3 * 1), 2));
});

test('salary for dailty contract work', function () {
    $contract = Contract::factory()->daily1()->createOne();

    $days = 300;
    Carbon::setTestNow($contract->start_date->clone()->addDays($days)->addHours(3));
    artisan('salaries-calculate');

    expect(Salary::count())->toBe($days)
        ->and(+Salary::sum('price'))->toBe(10000.0 * $days);

});

test('salary for random daily contract work', function () {
    $contract = Contract::factory()->randomSample1()->createOne();

    Carbon::setTestNow('2023-08-28 18:37:00');
    artisan('salaries-calculate');
    expect(Salary::count())->toBeGreaterThanOrEqual(1);
//    dump(Salary::query()->orderBy('end_date', 'desc')->get()->toArray());


});
test('random test', function () {
    $contract = Contract::factory()->randomSample1()->createOne();
    Carbon::setTestNow('2023-08-28 18:37:00');
    artisan('salaries-calculate');
    expect(Salary::count())->toBeGreaterThanOrEqual(1);
});
