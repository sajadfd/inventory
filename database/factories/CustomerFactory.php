<?php

namespace Database\Factories;

use App\Enums\UserType;
use App\Models\User;
use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ar_SA')->unique()->name(),
            'address' => fake('ar_SA')->address(),
            'phone' => fake('ar_SA')->unique()->phoneNumber(),
            'thumbnail' => $url = FakeImagesService::make('customer'),
            'image' => $url,
        ];
    }
}
