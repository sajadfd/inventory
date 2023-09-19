<?php

namespace Database\Seeders;

use App\Models\Stockholder;
use Database\Factories\StockholderFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockholderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stockholder::factory(3)->create();
    }
}
