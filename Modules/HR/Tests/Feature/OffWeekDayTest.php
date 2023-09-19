<?php
declare(strict_types=1);

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\Seeders\OffWeekDaySeeder;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffWeekDay;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

/** @var OffWeekDay $offWeekDay */


uses(RefreshDatabase::class, TestCase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
});
test("OffWeekDay can be indexed", function () {
    seed(OffWeekDaySeeder::class);
    $res = get('api/off_week_days');
    $res->assertStatus(200);
});

test("OffWeekDay can be showed", function () {
    /** @var OffWeekDay $offWeekDay */

    seed(OffWeekDaySeeder::class);
    $offWeekDay = OffWeekDay::query()->inRandomOrder()->first();
    $res = get('api/off_week_days/' . $offWeekDay->id);
    $res->assertStatus(200);
});

test("OffWeekDay can successfully created", function () {
    $contract = Contract::factory()->sample1()->createOne();
    $res = post('api/off_week_days', [
        "contract_id" => $contract->id,
        "consider_as_presence" => 1,
        "day" => 5,
        "notes" => "bla"
    ]);
    $res->assertStatus(200);
    post('api/off_week_days', [
        "contract_id" => $contract->id,
        "day" => 5,
    ])->assertStatus(422);
    expect(OffWeekDay::count())->toEqual(1);
});
test("OffWeekDay can successfully created for all contracts", function () {
    Contract::factory(10)->create();
    $res = post('api/off_week_days/many', [
        "consider_as_presence" => 1,
        "day" => 6,
        "notes" => "bla"
    ]);
    $res->assertStatus(200);
    expect(OffWeekDay::count())->toEqual(Contract::query()->isNotEnded()->count());
});
test("OffWeekDay can successfully created for multiple contracts", function () {
    Contract::factory(10)->create();
    $res = post('api/off_week_days/many', [
        'contract_ids' => Contract::inRandomOrder()->isNotEnded()->pluck('id')->take($cnt = rand(1, 5))->toArray(),
        "consider_as_presence" => 1,
        "day" => 6,
        "notes" => "bla"
    ]);
    $res->assertStatus(200);
    expect(OffWeekDay::count())->toEqual($cnt);
});

test("OffWeekDay can successfully updated", function () {
    /** @var Contract $contract */
    $contract = Contract::factory()->createOne();
    /** @var OffWeekDay $offWeekDay */
    $offWeekDay = OffWeekDay::factory()->createOne();
    $res = put('api/off_week_days/' . $offWeekDay->id, [
        "contract_id" => $contract->id,
        "day" => array_filter([0, 2, 3, 4], fn($i) => $i !== $offWeekDay->day)[rand(0, 2)],
    ]);
    $res->assertStatus(200);
});

test("OffWeekDay can successfully deleted", function () {
    /** @var OffWeekDay $offWeekDay */
    $offWeekDay = OffWeekDay::factory()->create();
    $res = delete("api/off_week_days/" . $offWeekDay->id);
    $res->assertStatus(200);
});
