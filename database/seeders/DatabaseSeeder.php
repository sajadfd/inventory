<?php

namespace Database\Seeders;

use App\Enums\ExpenseSource;
use App\Enums\UserType;
use App\Models\CartItem;
use App\Models\GlobalOption;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Modules\HR\Database\Seeders\HRDatabaseSeeder;

class DatabaseSeeder extends Seeder
{

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /** @var User $user */
        $user = User::query()->first();
        auth()->login($user);
        UserFactory::new(['type' => UserType::INVENTORY_ADMIN])->create();
        User::factory()->driver()->create(['username' => 'driver_user']);
        User::factory()->driver()->create(['phone' => '07712345678']);

        $this->call([
            GlobalOptionSeeder::class,
            NotificationSeeder::class,
            StockholderSeeder::class,
            MechanicSeeder::class,
            ExpenseSeeder::class,
            BrandSeeder::class,
            ProductLocationSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ProductUnitSeeder::class,
            ColorSeeder::class,
            DiagnosisSeeder::class,
            CarTypeSeeder::class,
            CarModelSeeder::class,
            ServiceSeeder::class,
            SupplierSeeder::class,
            PurchaseListSeeder::class,
            CustomerSeeder::class,
            CarSeeder::class,
            SaleListSeeder::class,
            CartSeeder::class,
            CartItemSeeder::class,
            DriverSeeder::class,
            OrderSeeder::class,
        ]);

        $this->call([
            HRDatabaseSeeder::class,
        ]);


    }
}
