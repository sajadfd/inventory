<?php

namespace Database\Factories;

use App\Enums\ProductTransactionEnum;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InitialStore;
use App\Models\Product;
use App\Models\ProductLocation;
use App\Models\User;
use App\Services\FakeImagesService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{

    protected static $names = [
        "مضخة_زيت",
        "فلتر_زيت",
        "بوجيهات",
        "سير_محرك",
        "تروس_محرك",
        "بلوف_محرك",
        "إطارات",
        "جنوط",
        "مسامير_عجلات",
        "توازن_عجلات",
        "محور_عجلات",
        "أقراص_فرامل",
        "أقمشة_فرامل",
        "سيطرة_فرامل",
        "خرطوش_فرامل",
        "مساعدين_فرامل",
        "بطارية",
        "مولد_كهرباء",
        "بواجي",
        "كهربائيات_محرك",
        "مساعدين_تعليق",
        "كراسي_تعليق",
        "بالونات_تعليق",
        "مقصات",
        "قضبان_ستابيليزر",
        "مضخة_زيت",
        "فلتر_زيت",
        "بوجيهات",
        "سير_محرك",
        "تروس_محرك",
        "بلوف_محرك",
        "إطارات",
        "جنوط",
        "مسامير_عجلات",
        "توازن_عجلات",
        "محور_عجلات",
        "أقراص_فرامل",
        "أقمشة_فرامل",
        "سيطرة_فرامل",
        "خرطوش_فرامل",
        "مساعدين_فرامل",
        "بطارية",
        "مولد_كهرباء",
        "بواجي",
        "كهربائيات_محرك",
        "مساعدين_تعليق",
        "كراسي_تعليق",
        "بالونات_تعليق",
        "مقصات",
        "قضبان_ستابيليزر",
        "شمعات_إشعال",
        "مكابح_يدوية",
        "عوازل_حرارية",
        "صمامات_عادم",
        "زجاج_أمامي",
        "زجاج_خلفي",
        "فلتر_هواء",
        "بوابة_هواء",
        "بخاخات_وقود",
        "مضخة_وقود",
        "فرامل_يدوية",
        "صفائح_فرامل",
        "كابلات_فرامل",
        "مفصلات_باب",
        "زجاجات_مياه_غسيل",
        "فلتر_مكيف",
        "مروحة_تبريد",
        "بلف_تبريد",
        "خراطيم_تبريد",
        "ثلاجة_تبريد",
        "كمبريسور_تكييف",
        "فحمات_فرامل",
        "مرايا_جانبية",
        "مقابض_باب",
        "مسجل_صوت",
        "سيور_ملابس_أمان",
        "كراسي_سيارة",
        "مصابيح_أمامية",
        "مصابيح_خلفية"
    ];

    public function definition(): array
    {

        return [
            'name' => config('app.env') === 'testing' ? fake()->unique()->sentences(3, true) : $this->faker->unique()->randomElement(self::$names) . ' ' . rand(0, 3),
            'category_id' => (Category::query()->inRandomOrder()->first() ?: CategoryFactory::new()->createOne())->id,
            'thumbnail' => $url = FakeImagesService::make('product'),
            'image' => $url,
            'sale_price' => rand(1, 5) + 2.5,
            'sale_currency' => 'usd',
            'brand_id' => Brand::query()->inRandomOrder()->firstOrCreate(Brand::factory()->makeOne()->toArray()),
            'product_location_id' => ProductLocation::query()->inRandomOrder()->first() ?: ProductLocation::factory()->createOne(),
            'is_visible_in_store' => $this->faker->boolean(),
            'store' => $this->faker->numberBetween(0, 15),
            'depletion_alert_at' => rand(-1, 5),
        ];

    }

    public function configure()
    {
        return $this->afterCreating(function (Product $product) {
            if ($product->store > 0) {
                $initialStore = $product->initialStore()->create([
                    'price' => $product->sale_price - (rand(5, 10) / 10),
                    'currency_value' => 1450,
                    'count' => $product->store,
                ]);
                $product->transactions()->create([
                    'count' => $product->store,
                    'type' => ProductTransactionEnum::Initial,
                    'targetable_id' => $initialStore->id,
                    'targetable_type' => InitialStore::class,
                ]);
            }
        });
    }
}
