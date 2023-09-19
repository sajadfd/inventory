<?php

use App\Enums\UserType;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarType;
use App\Models\Color;
use App\Models\Customer;
use Database\Factories\CarFactory;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $user = UserFactory::new(['type' => UserType::SuperAdmin])->create();
    Sanctum::actingAs($user);
});

test('car index', function () {
    CarFactory::times(3)->create();
    $res = get('api/cars')->assertStatus(200);
    expect($res->json('data.total'))->toEqual(Car::count());
});

test('car index with filters', function () {
    CarFactory::times(3)->create();
    get('api/cars?filter[customer_id]=1&filter[plate_number]=a2323&sorts=-color_id')->assertStatus(200);
});

test('car can be created', function () {
    $res = post('api/cars', [
        'customer_id' => $customer_id = Customer::factory()->createOne()->id,
        'car_type_id' => CarType::factory()->createOne()->id,
        'color_id' => Color::factory()->createOne()->id,
        'car_model_id' => CarModel::factory()->createOne()->id,
        'model_year' => 2010,
        'plate_number' => 'a234',
        'vin' => 'abcdefg',
    ]);
    $res->assertStatus(200);
    expect(Car::count())->toEqual(1)
        ->and(Car::first()->customer_id)->toEqual($customer_id);
});


test('car can be created with customer user', function () {
    $customerUser = \App\Models\User::factory()->customer()->createOne();
    Sanctum::actingAs($customerUser);
    $res = post('api/cars', [
        'car_type_id' => CarType::factory()->createOne()->id,
        'color_id' => Color::factory()->createOne()->id,
        'car_model_id' => CarModel::factory()->createOne()->id,
        'model_year' => 2010,
        'plate_number' => 'a234',
        'vin' => 'abcdefg',
    ]);
    $res->assertStatus(200);
    expect(Car::count())->toEqual(1)
        ->and(Car::first()->customer_id)->toEqual($customerUser->customer->id);
});

