<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->decimal('count',10,2);
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('currency', ['usd', 'iqd'])->default('iqd');
            $table->decimal('currency_value', 10, 2)->default(1);
            $table->string('notes')->nullable();
            $table->foreignIdFor(Order::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductUnit::class)->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
