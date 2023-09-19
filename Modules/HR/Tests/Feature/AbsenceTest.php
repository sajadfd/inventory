<?php

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\Seeders\AbsenceSeeder;
use Modules\HR\Entities\Absence;
use Modules\HR\Entities\Attendance;
use Modules\HR\Entities\Contract;
use Modules\HR\Entities\OffDate;
use Modules\HR\Entities\Salary;
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

test("Absence can be indexed", function () {
    seed(AbsenceSeeder::class);
    $res = get('api/absences');
    $res->assertStatus(200);
});

test("Absence can be showed", function () {
    seed(AbsenceSeeder::class);
    $absence = Absence::query()->inRandomOrder()->first();
    $res = get('api/absences/' . $absence->id);
    $res->assertStatus(200);
});

test("absence can successfuly deleted", function () {
    $contract = Contract::factory()->createOne();
    $absence = Absence::factory()->for($contract)->create();
    $salary = Salary::factory()->for($contract)->createOne();

    $salary->absences()->attach($absence->id);
    delete("api/absences/" . $absence->id)->assertStatus(422);
    $salary->delete();
    delete("api/absences/" . $absence->id)->assertStatus(200);
});

test('store validates start date less than end date', function () {
    \Date::setTestNow('2023-05-01');
    post('api/absences', [
        "contract_id" => Contract::factory()->sample1()->createOne()->id,
        "start_date" => "2023-05-05 19:23:30",
        "end_date" => "2023-05-04 19:23:30",
    ])->assertStatus(422);
});

test('absence doesnt overlaps', function () {
    Carbon::setTestNow('2023-05-06');
    $res = post('api/absences', [
        "contract_id" => Contract::factory()->sample1()->createOne()->id,
        "start_date" => "2023-05-05 19:23:30",
        "end_date" => "2023-05-07 19:23:30",
    ]);
    $res->assertStatus(200);
});
test('absence creation must be before overlaps', function () {
    Carbon::setTestNow('2023-05-01');
    $res = post('api/absences', [
        "contract_id" => Contract::factory()->sample1()->createOne()->id,
        "start_date" => "2023-05-05 19:23:30",
        "end_date" => "2023-05-07 19:23:30",
    ]);
    $res->assertStatus(422);
});

test('store absence overlapping attendance or offdates cause error', function () {
    \Date::setTestNow('2023-05-01');
    $contract = Contract::factory()->sample1()->createOne();
    OffDate::factory()->createOne([
        "contract_id" => $contract->id,
        "start_date" => "2023-05-01 19:23:30",
        "end_date" => "2023-05-05 19:23:30",
    ]);

    Attendance::factory()->createOne([
        "contract_id" => $contract->id,
        "start_date" => "2023-05-10 19:23:30",
        "end_date" => "2023-05-15 19:23:30",
    ]);
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-01 19:23:30",
        "end_date" => "2023-05-03 19:23:30",
    ])->assertStatus(422);
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-14 19:23:30",
        "end_date" => "2023-05-16 19:23:30",
    ])->assertStatus(422);
});

test('store overlapping absences cause validation error', function () {
    \Date::setTestNow('2023-05-01');
    $contract = Contract::factory()->sample1()->createOne();
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-01 19:23:30",
        "end_date" => "2023-05-05 19:23:30",
    ])->assertStatus(200);
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-05 19:23:30",
        "end_date" => "2023-05-17 19:23:30",
    ])->assertStatus(422);
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-03 19:23:30",
        "end_date" => "2023-05-04 19:23:30",
    ])->assertStatus(422);
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-04-05 19:23:30",
        "end_date" => "2023-06-04 19:23:30",
    ])->assertStatus(422);
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-04-05 19:23:30",
        "end_date" => "2023-05-02 19:23:30",
    ])->assertStatus(422);
    post('api/absences', [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-03 19:23:30",
        "end_date" => "2023-05-10 19:23:30",
    ])->assertStatus(422);
});

test("absence can successfully updated", function () {

    \Date::setTestNow('2023-05-05');
    $contract = Contract::factory()->sample1()->createOne();
    $absence = Absence::factory()->createOne([
        "contract_id" => $contract->id,
        "start_date" => "2023-05-01 19:23:30",
        "end_date" => "2023-05-05 19:23:30",
    ]);
    put('api/absences/' . $absence->id, [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-01 19:23:30",
        "end_date" => "2023-05-05 19:23:30",
        "notes" => "not nice"
    ])->assertStatus(200);
    $res = put('api/absences/' . $absence->id, [
        "contract_id" => $contract->id,
        "start_date" => "2023-05-03 19:23:30",
        "end_date" => "2023-05-20 19:23:30",
        "notes" => "not nice"
    ]);
    $res->assertStatus(200);
});
