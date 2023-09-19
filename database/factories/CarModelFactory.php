<?php

namespace Database\Factories;

use App\Models\User;
use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CarModel>
 */
class CarModelFactory extends Factory
{

    protected static $names = [
        "الف روميو",
        "أودي",
        "ايسوزو",
        "ام جى",
        "أوبل",
        "بروتون",
        "بى ام دبليو",
        "بريليانس",
        "بى واى دى",
        "بيجو",
        "بورش",
        "تويوتا",
        "دايو",
        "دايهاتسو",
        "دودج",
        "جيلى",
        "جاكوار",
        "جيب",
        "رينو",
        "سايبا",
        "سكودا",
        "اسبرانزا",
        "سانج يونغ",
        "سوبارو",
        "شيرى",
        "شيفروليه",
        "سيتروين",
        "فورد",
        "سيات",
        "سوزوكى",
        "كرايسلر",
        "فيات",
        "كيا",
        "ﻻدا",
        "فولكسفاجن",
        "فولفو",
        "ﻻند روفر",
        "مازدا",
        "مرسيدس بنز",
        "ميني كوبر",
        "ميتسوبيشى",
        "نيسان",
        "هوندا",
        "هامر",
        "هيونداي",
    ];

    public function definition(): array
    {
        return [
            'name' =>config('app.env')=== 'testing' ? fake()->unique()->sentences(3,true) : $this->faker->unique()->randomElement(self::$names) . ' ' . rand(0, 10),
            'thumbnail' => $url = FakeImagesService::make('car_model'),
            'image' => $url,
            'created_by' => 1,
        ];
    }
}
