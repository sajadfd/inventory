<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mechanic;

class MechanicSeeder extends Seeder
{
    public function run(): void
    {
        Mechanic::factory(5)->create();
    }
}
