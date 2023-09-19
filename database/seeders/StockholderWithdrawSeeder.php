<?php

namespace Database\Seeders;

use App\Models\StockholderWithdraw;
use Illuminate\Database\Seeder;

class StockholderWithdrawSeeder extends Seeder
{

    public function run(): void
    {
        StockholderWithdraw::factory(5)->create();
    }
}
