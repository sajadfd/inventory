<?php

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\factories\ContractFactory;
use Modules\HR\Database\Seeders\AbsenceSeeder;
use Modules\HR\Database\Seeders\ContractSeeder;
use Modules\HR\Database\Seeders\OffDateSeeder;
use Modules\HR\Database\Seeders\OffWeekDaySeeder;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\Employer;
use Modules\HR\Entities\OffWeekDay;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class, TestCase::class);
//uses(DatabaseSeeder::class);

beforeEach(function () {
    Sanctum::actingAs(User::first());
});


test("contract can be indexed", function () {
    seed(ContractSeeder::class);
    $res = get('api/contracts');
    $res->assertStatus(200);
});

test("contract can be showed", function () {
    seed(ContractSeeder::class);
    $contract = Contract::query()->inRandomOrder()->first();
    $res = get('api/contracts/' . $contract->id);
    $res->assertStatus(200);
});
test("contract can be showed with date filter", function () {
    Contract::factory()->createOne([
        'start_date' => '2020-01-01',
        'end_date' => '2020-12-31'
    ]);
    seed([AbsenceSeeder::class, OffDateSeeder::class, OffWeekDaySeeder::class]);
    $contract = Contract::query()->inRandomOrder()->first();
    $res = get('api/contracts/' . $contract->id . "?filter_from=2020-01-01&filter_to=2020-01-31");

    dump($res->json());
    $res->assertStatus(200);
});

test("contract can successfully created", function () {
    $employer = Employer::factory()->createOne();
    $res = post('api/contracts', [
        "employer_id" => $employer->id,
        "start_date" => "2012-02-06 13:37:26",
        "end_date" => "2020-11-07 04:27:42",
        "salary_type" => "by_day",
        "salary_price" => "822159.70",
        "salary_currency" => "iqd",
        "day_work_hours" => "10.0",
        "day_work_start_hour" => "19.7",
        "track_by" => "attendances"

    ]);
    $res->assertStatus(200);
    expect(Contract::count())->toEqual(1);
});

test("contract can successfully updated", function () {
    $employer = Employer::factory()->createOne();
    Contract::factory()->createOne([
        'start_date' => now(),
        'end_date' => now()->addDay()
    ]);
    $contract = Contract::query()->inRandomOrder()->first();
    $res = put('api/contracts/' . $contract->id, [
        "employer_id" => $employer->id,
        "start_date" => "2000-07-25 10:02:19",
        "end_date" => "2012-12-03 05:00:27",
        "salary_type" => "by_day",
        "salary_price" => "819464.07",
        "salary_currency" => "usd",
        "day_work_hours" => "19.0",
        "day_work_start_hour" => "15.0",
        "track_by" => "absences",
    ]);
    $res->assertStatus(200);
});

test("contract can successfully deleted", function () {
    $contract = Contract::factory()->createOne([
        'start_date' => now(),
        'end_date' => now()->addDay()
    ]);
    delete("api/contracts/" . $contract->id)->assertStatus(200);
});

test("contract cannot be deleted after 1 day", function () {
    $contract = Contract::factory()->createOne([
        'start_date' => now()->subDays(2),
        'end_date' => now()->addDay()
    ]);
    delete("api/contracts/" . $contract->id)->assertStatus(422);
});
