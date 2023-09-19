<?php

namespace Database\Factories;

use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Supplier>
 */
class SupplierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => fake('ar_SA')->unique()->name(),
            'phone' => fake('ar_SA')->unique()->phoneNumber(),
            'address' => fake('ar_SA')->unique()->address(),
            'thumbnail' => $url = FakeImagesService::make('supplier'),
            'image' => $url,
        ];
    }
}
