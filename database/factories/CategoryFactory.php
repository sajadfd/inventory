<?php

namespace Database\Factories;

use App\Models\User;
use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected static $names = [
        "هيكل السيارة",
        "غطاء محرك السيارة",
        "الغطاء الخلفي",
        "غطاء العجلات",
        "المتلاف الأمامي والخلفي",
        "الشبك الأمامي",
        "الشبك الخلفي",
        "حدادية",
        "تصنيف منتجات",
        "الملحمات",
        "الأجنحة",
        "الزجاج",
        "الأبواب",
        "الفرامل",
        "المحركات",
        "المنظفات",
        "المستودعات",
        "المقطورات",
        "الحدادية",
        "القشر الخارجي",
        "الاطارات",
        "يدات الابواب",
    ];

    public function definition(): array
    {

        return [
            'name' => $this->faker->unique()->randomElement(self::$names) . ' ' . rand(0, 10),
            'thumbnail' => $url = FakeImagesService::make('category'),
            'image' => $url,
        ];
    }
}
