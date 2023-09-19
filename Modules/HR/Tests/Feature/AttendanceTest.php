<?php

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\Seeders\AttendanceSeeder;
use Modules\HR\Entities\Attendance;
use Modules\HR\Entities\Contract;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

uses(RefreshDatabase::class , TestCase::class);

beforeEach(function (){
    Sanctum::actingAs(User::first());
});

test("Attendance can be indexed" , function (){
    seed(AttendanceSeeder::class);
    $res = get('api/attendances');
    $res->assertStatus(200);
});

test("Attendance can be showed" , function(){
    $attendance = Attendance::factory()->createOne();
    $res = get('api/attendances/' . $attendance->id);
    $res->assertStatus(200);
});

test("Attendance can successfully created"  , function (){
    $contract = Contract::factory()->createOne();
    $res = post('api/attendances', [
        "contract_id" => $contract->id ,
        "start_date" => $startDate = fake()->dateTimeBetween($contract->start_date, $contract->end_date->clone()->subDay())->format('Y-m-d H:i:s'),
        "end_date" => fake()->dateTimeBetween($startDate, $contract->end_date)->format('Y-m-d H:i:s'),
        "notes" => "not nice"
    ]);
    $res->assertStatus(200);
    expect(Attendance::count())->toEqual(1);
});

test("Attendance can successfully updated" , function (){
    $contract = Contract::factory()->active()->createOne();
    $attendance = Attendance::factory()->createOne();
    $res = put('api/attendances/' . $attendance->id , [
        "contract_id" => $contract->id ,
        "start_date" => $startDate = fake()->dateTimeBetween($contract->start_date, $contract->end_date->clone()->subDay())->format('Y-m-d H:i:s'),
        "end_date" => fake()->dateTimeBetween($startDate, $contract->end_date)->format('Y-m-d H:i:s'),
        "notes" => "not nice"
    ]);
    $res->assertStatus(200);
});

test("Attendance can successfully deleted" , function (){
    $attendance = Attendance::factory()->create();
    $res = delete("api/attendances/" . $attendance->id);
    $res->assertStatus(200);
});


