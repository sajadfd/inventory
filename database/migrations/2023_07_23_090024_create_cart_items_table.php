<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductUnit;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Cart::class);
            $table->foreignIdFor(Product::class);
            $table->foreignIdFor(ProductUnit::class)->nullable();
            $table->decimal('count', 10, 2);
            $table->text('notes')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
