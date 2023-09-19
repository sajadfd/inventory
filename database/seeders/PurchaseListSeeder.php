<?php

namespace Database\Seeders;

use App\Models\PurchaseItem;
use App\Models\PurchaseList;
use App\Models\SaleItem;
use App\Models\SaleList;
use App\Models\ServiceItem;
use App\Models\User;
use App\Services\ProductStoreService;
use Database\Factories\PurchaseItemFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseListSeeder extends Seeder
{
    public function run(): void
    {
        if (!auth()->check()) {
            auth()->login(User::first());
        }

        PurchaseList::factory(5)->setWithItems(true)->create()->each(function (PurchaseList $purchaseList) {
            if (rand(0, 1)) {
                $purchaseList->confirm(rand(0, 1));
            }
        });
    }
}
