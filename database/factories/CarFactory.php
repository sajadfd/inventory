<?php

namespace Database\Factories;

use App\Models\Car;
use App\Models\CarModel;
use App\Models\CarType;
use App\Models\Color;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => (Customer::query()->inRandomOrder()->first() ?: Customer::factory()->createOne())->id,
            'car_type_id' => CarType::query()->inRandomOrder()->firstOrCreate(CarType::factory()->makeOne()->toArray())->id,
            'car_model_id' => CarModel::query()->inRandomOrder()->firstOrCreate(CarModel::factory()->makeOne()->toArray())->id,
            'color_id' => Color::query()->inRandomOrder()->firstOrCreate(Color::factory()->makeOne()->toArray())->id,
            'plate_number' => $this->faker->randomLetter() . '-' . $this->faker->randomNumber(6),
            'model_year' => $this->faker->numberBetween(2000, 2024),
            'vin' => $this->faker->shuffleString('ABC_DEF1234567890123'),
            'meter_number' => $this->faker->numberBetween(1000, 50000),
        ];
    }
}
