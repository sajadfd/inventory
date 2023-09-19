<?php

namespace Database\Seeders;

use App\Enums\GlobalOptionEnum;
use App\Models\GlobalOption;
use Faker\Provider\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GlobalOptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = \Faker\Factory::create('ar_SA');

        GlobalOption::query()->find(GlobalOptionEnum::HeaderImage)->update([
//            'value' => '/files/images/' . $faker->image(public_path('files/images'), 640, 200, 'invoice header', false)
            'value' => '/files/' . 'header.png'
        ]);
        GlobalOption::query()->find(GlobalOptionEnum::FooterImage)->update([
//            'value' => '/files/images/' . $faker->image(public_path('files/images'), 640, 100, 'invoice footer', false)
            'value' => '/files/' . 'footer.png'
        ]);
    }
}
