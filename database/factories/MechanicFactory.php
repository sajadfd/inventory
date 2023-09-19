<?php
declare(strict_types=1);
namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mechanic>
 */
class MechanicFactory extends Factory
{
    public function definition(): array
    {
        $faker = \Faker\Factory::create('ar_SA');
        return [
           "name" => $faker->unique()->name()
        ];
    }
}
