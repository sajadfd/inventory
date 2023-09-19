<?php

use App\Enums\UserType;
use App\Models\Customer;
use App\Models\User;
use Database\Factories\UserFactory;
use Database\Seeders\BrandSeeder;
use Database\Seeders\CarModelSeeder;
use Database\Seeders\CarSeeder;
use Database\Seeders\CarTypeSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\ColorSeeder;
use Database\Seeders\CustomerSeeder;
use Database\Seeders\DiagnosisSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\SaleListSeeder;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
    Sanctum::actingAs(User::first());
});

test('customers index', function () {
    $this->seed([CustomerSeeder::class]);
    $res = $this->get('api/customers')->assertStatus(200);
    expect($res->json('data.total'))->toEqual(Customer::count());
});

test('customers can be created', function () {
    $image = UploadedFile::fake()->image('img.png');
    $res = $this->post('api/customers', ['name' => 'Customer 1', 'image' => $image, 'phone' => 1234, 'address' => 'address']);
    $res->assertStatus(200);
    expect(Customer::count())->toEqual(1);
    expect(Str::startsWith($res->json('data.image'), config('app.url')))->toBeTrue();
});

test('customer can be updated', function () {
    $customer = Customer::factory()->create();
    $res = $this->put('api/customers/' . $customer->id, ['name' => $newName = 'Customer 1', 'image' => $customer->image]);
    $res->assertStatus(200);
    $customer->refresh();
    expect(Customer::count())->toEqual(1);
    expect($customer->name)->toEqual($newName);
});

test('customer can be deleted', function () {
    $this->seed([
        BrandSeeder::class,
        CategorySeeder::class,
        ProductSeeder::class,
        CustomerSeeder::class,
        ColorSeeder::class,
        DiagnosisSeeder::class,
        CarModelSeeder::class,
        CarTypeSeeder::class,
        ServiceSeeder::class,
        CarSeeder::class,
        SaleListSeeder::class
    ]);
    $customer = Customer::factory()->create();

    $customerCounts = Customer::count();
    $customer = Customer::query()->whereHas('saleLists')->inRandomOrder()->first();
    $res = $this->delete('api/customers/' . $customer->id);
    $res->assertStatus(422);

    $customer = Customer::query()->whereDoesntHave('saleLists')->inRandomOrder()->first();
    $res = $this->delete('api/customers/' . $customer->id);
    $res->assertStatus(200);

    expect(Customer::count())->toEqual($customerCounts - 1);
});
