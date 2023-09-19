<?php

namespace Database\Factories;

use App\Enums\CartTypeEnum;
use App\Enums\SaleType;
use App\Enums\UserType;
use App\Models\Car;
use App\Models\Diagnosis;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Cart>
 */
class CartFactory extends Factory
{

    public function definition(): array
    {
        $type = rand(0, 1) ? CartTypeEnum::StoreSale : CartTypeEnum::InventorySale;

        $user = User::query()->inRandomOrder()->where('type', UserType::Customer)->whereDoesntHave('cart')->first()
            ?: User::factory()->createOne(['type' => UserType::Customer]);
        return [
                'type' => $type,
                'notes' => $this->faker->text(10),
                'user_id' => $user->id,
            ] + ($type === CartTypeEnum::InventorySale ? [
                'diagnosis_id' => (Diagnosis::query()->inRandomOrder()->first() ?: Diagnosis::factory()->createOne())->id,
                'car_id' => ($user->customer->cars()->inRandomOrder()->first() ?: Car::factory()->for($user->customer)->createOne())->id,
            ] : []);
    }
}
