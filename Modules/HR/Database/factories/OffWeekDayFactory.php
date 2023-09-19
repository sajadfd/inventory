<?php

namespace Modules\HR\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\HR\Entities\Contract;

class OffWeekDayFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = \Modules\HR\Entities\OffWeekDay::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            "contract_id" => (Contract::query()->inRandomOrder()->first() ?: ContractFactory::new()->createOne())->id,
            "day" => fake()->numberBetween(1, 7),
            "notes" => fake()->paragraph(),
        ];
    }
}

