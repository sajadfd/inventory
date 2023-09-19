<?php

namespace Database\Factories;

use App\Models\User;
use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarType>
 */
class CarTypeFactory extends Factory
{

    protected static $names = [

        "  مرسيدس بنز ",
        "فولكس فاجن",
        "بي إم دبليو",
        "أوبل",
        "أودي",
        "دايملر كرايسلر",
        "أستون مارتن",
        "لكزس",
        " ألفا روميو ",
        "بنتلي",
        "بوغاتي",
        "كاديلاك",
        "شيفروليه",
        "سيتروين",
        "دودج",
        "فيراري",
        "فيات",
        "فورد",
        "جنرال موتورز ",
        "هامر",
        "هيونداي",
        "سيات",
        "جاغوار",
        "جيب",
        " لاند روفر",
        "لوتس",
        "مازيراتي",
        "مازدا",
    ];

    public function definition(): array
    {
        return [
            'name' =>  config('app.env')=== 'testing' ? fake()->unique()->word() : $this->faker->unique()->randomElement(self::$names) . ' ' . rand(0, 10),
            'thumbnail' => $url = FakeImagesService::make('car_type'),
            'image' => $url,
        ];
    }
}
