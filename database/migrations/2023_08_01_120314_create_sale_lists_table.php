<?php

use App\Enums\SaleType;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Diagnosis;
use App\Models\Mechanic;
use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sale_lists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', SaleType::getAllValues())->default(SaleType::StoreSale->value);
            $table->string('notes')->nullable();
            $table->enum('currency', ['iqd', 'usd'])->default('iqd');
            $table->boolean('is_confirmed')->default(false);
            $table->foreignIdFor(Customer::class);
            $table->foreignIdFor(Diagnosis::class)->nullable();
            $table->foreignIdFor(Car::class)->nullable();
            $table->foreignIdFor(Mechanic::class)->nullable();
            $table->foreignIdFor(Order::class)->nullable();
            $table->foreignId('created_by');
            $table->timestamp('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_lists');
    }
};
