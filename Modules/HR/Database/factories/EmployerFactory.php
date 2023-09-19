<?php

namespace Modules\HR\Database\factories;

use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployerFactory extends Factory
{

    protected $model = \Modules\HR\Entities\Employer::class;

    public function definition()
    {
        return [
            "name" => fake('ar_SA')->unique()->name(),
            "phone" => fake('ar_SA')->unique()->phoneNumber(),
            "address" => fake('ar_SA')->address(),
            'thumbnail' => $url = FakeImagesService::make('employer'),
            'image' => $url,
        ];
    }
}

