<?php

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\Seeders\OffDateSeeder;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class, TestCase::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
});

test("OffDate can be indexed", function () {
    seed(OffDateSeeder::class);
    $res = get('api/off_dates');
    $res->assertStatus(200);
});

test("OffDate can be showed", function () {
    seed(OffDateSeeder::class);
    $offDate = OffDate::query()->inRandomOrder()->first();
    $res = get('api/off_dates/' . $offDate->id);
    $res->assertStatus(200);
});

test("OffDate can successfully created", function () {
    $contract = Contract::factory()->active()->createOne();
    $res = post('api/off_dates', [
        "contract_id" => $contract->id,
        "consider_as_presence" => 1,
        "start_date" => $startDate = fake()->dateTimeBetween($contract->start_date, $contract->end_date->clone()->subDay(1))->format('Y-m-d H:i:s'),
        "end_date" => fake()->dateTimeBetween($startDate, $contract->end_date)->format('Y-m-d H:i:s'),
        "notes" => "not nice"
    ]);
    $res->assertStatus(200);
    expect(OffDate::count())->toEqual(1);
});


test("OffDate can successfully created for all", function () {
    $contracts = Contract::factory(10)->active()->create();
    $contractsStartDate = $contracts->sortByDesc('start_date')->first()->start_date;
    $contractsEndDate = $contracts->sortBy('end_date')->first()->end_date;
    $res = post('api/off_dates/many', [
        "consider_as_presence" => 1,
        "start_date" => $startDate = fake()->dateTimeBetween($contractsStartDate, $contractsEndDate->clone()->subDay(1))->format('Y-m-d H:i:s'),
        "end_date" => fake()->dateTimeBetween($startDate, $contractsEndDate)->format('Y-m-d H:i:s'),
        "notes" => "not nice"
    ]);
    $res->assertStatus(200);
    expect(OffDate::count())->toEqual(10);
});


test("OffDate can successfully updated", function () {
    $contract = Contract::factory()->active()->createOne();
    $offDate = OffDate::factory()->createOne();
    $res = put('api/off_dates/' . $offDate->id, [
        "contract_id" => $contract->id,
        "start_date" => $startDate = fake()->dateTimeBetween($contract->start_date, $contract->end_date->clone()->subDay(1))->format('Y-m-d H:i:s'),
        "end_date" => fake()->dateTimeBetween($startDate, $contract->end_date)->format('Y-m-d H:i:s'),
        "notes" => "not nice"
    ]);
    $res->assertStatus(200);
});

test("OffDate can successfully deleted", function () {
    $offDate = OffDate::factory()->create();
    $res = delete("api/off_dates/" . $offDate->id);
    $res->assertStatus(200);
});


