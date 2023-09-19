<?php

namespace Database\Factories;

use App\Enums\ExpenseSource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{

    public static $descriptionsDataset = [
        'رواتب موظفين',
        'طلاء الورشة',
        'تصليح المكتب',
        'شراء انارة',
        'ورق للطابعة',
        'شراء ماء وشاي',
        'غداء الموظفين'
    ];

    public function definition(): array
    {
        return [
            'description' => $this->faker->randomElement(ExpenseFactory::$descriptionsDataset),
            'source' => $this->faker->randomElement(ExpenseSource::getAllValues()),
            'price' => rand(1500, 500000),
            'date' => $this->faker->dateTimeBetween('-1 year',),
            'currency' => 'iqd',
            'currency_value' => 1450,
        ];
    }
}
