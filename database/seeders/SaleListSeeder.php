<?php

namespace Database\Seeders;

use App\Enums\SaleType;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\ServiceItem;
use App\Models\User;
use App\Services\ProductStoreService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SaleListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!auth()->check()) {
            $user = User::query()->first();
            auth()->login($user);
        }
        DB::beginTransaction();
        SaleList::factory(5)->setWithItems(true)->create()->each(function (SaleList $saleList) {
            if (rand(0, 1)) {
                $saleList->confirm(rand(0, 1));
            }
        });
        DB::commit();

    }
}
