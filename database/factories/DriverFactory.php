<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\User;
use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ar_SA')->name(),
            'address' => fake('ar_SA')->address(),
            'phone' => fake('ar_SA')->phoneNumber(),
            'thumbnail' => $url = FakeImagesService::make('driver'),
            'image' => $url,
            'user_id' => User::where('type', UserType::Driver)->inRandomOrder()->whereDoesntHave('driver')->first()?->id
                ?: User::factory()->driver()->createOne()->id,
        ];
        // ];
    }
}
