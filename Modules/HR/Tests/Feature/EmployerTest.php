<?php

namespace Modules\HR\Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Modules\HR\Database\Seeders\ContractSeeder;
use Modules\HR\Database\Seeders\EmployerSeeder;
use Modules\HR\Entities\Employer;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\seed;

beforeEach(function () {
    Sanctum::actingAs(User::first());
});

uses(
    TestCase::class,
    RefreshDatabase::class
);

test("employer can be indexed", function () {
    seed([EmployerSeeder::class, ContractSeeder::class]);
    $res = get('api/employers');
    $res->assertStatus(200);
});

test("employer can be showed", function () {
    seed([EmployerSeeder::class, ContractSeeder::class]);
    $employer = Employer::query()->inRandomOrder()->first();
    $res = get('api/employers/' . $employer->id);
    $res->assertStatus(200);
});

test("employer can successfully created", function () {

    $image = UploadedFile::fake()->image('img.png');

    $res = post('api/employers', ["name" => "employer 1", 'phone' => '12345678', 'address' => 'address', 'image' => $image]);
    $res->assertStatus(200);
    expect(Employer::count())->toEqual(1)
        ->and(Str::startsWith($res->json('data.image'), config('app.url')))->toBeTrue();

});

test('employer cannot be created with exiting name', function () {
    $res = post('api/employers', ["name" => "employer", 'phone' => '12345678']);
    $res->assertStatus(200);
    $res = post("api/employers", ["name" => "employer", 'phone' => '12345678']);
    $res->assertStatus(422);
    $this->assertDatabaseHas('employers', ['name' => "employer"]);
});

test("employer can successfuly updated", function () {
    seed([EmployerSeeder::class, ContractSeeder::class]);
    $employer = Employer::query()->inRandomOrder()->first();
    $res = put("api/employers/" . $employer->id, [
        "name" => "NewEmployer",
        'phone' => '12345678'
    ]);
    $res->assertStatus(200);
    $employer->refresh();
    expect($employer->name)->toEqual('NewEmployer');
});

test('employer cannot be updated to existing name', function () {
    $employer1 = Employer::factory()->create();
    $employer2 = Employer::factory()->create();

    $res = put('api/employers/' . $employer2->id, [
        "name" => $employer1->name,
        'phone' => $employer1->phone
    ]);

    $res->assertStatus(422);
    $this->assertDatabaseHas('employers', ['name' => $employer2->name]);
});

test("employer can successfuly deleted", function () {
    $employer = Employer::factory()->create();
    $res = delete("api/employers/" . $employer->id);
    $res->assertStatus(200);
});
