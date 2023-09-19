<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{

    protected static $names = ["بوش",
        "دينسو",
        "موتوركرافت",
        "مانلي",
        "فيليبس",
        "هيلا",
        "نجل",
        "كاسترول",
        "موبيل",
        "شل",
        "بريدجستون",
        "ميشلان",
        "جوديير",
        "فالفولين",
        "بيريللي",
        "فيرو",
        "بيكو",
        "كنجستون",
        "سكف",
        "كونتيننتال",
        "موتوكرافت",
        "إنتريدي",
        "فيبا",
        "أساهي",
        "أيسوزو",
        "ماغنا",
        "زيكو",
        "بريما",
        "نافاكو",
        "بيركا",
        "جيب",
        "شافت",
        "هيلفيغر",
        "واجنر",
        "أكسل",
        "أوسرام",
        "ساكورا",
        "فيكو",
        "فليتغار",
        "أتسكو",
        "ويكو",
        "كايسر",
        "ديلكو",
        "مانرو",
        "هيبري",
        "جايكو",
        "يوكوهاما"
    ];

    public function definition(): array
    {
        return [
            'name' => config('app.env')=== 'testing' ? fake()->unique()->sentences(3,true) : $this->faker->unique()->randomElement(self::$names) . ' ' . rand(0, 10),
        ];
    }
}
