<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->foreignId('product_unit_id')->nullable();
            $table->foreignId('purchase_list_id');
            $table->decimal('count', 10, 2);
            $table->decimal('back_count', 10, 2)->default(0);
            $table->decimal('free_count', 10, 2)->default(0);
            $table->decimal('used', 28, 14)->default(0);
            $table->double('price');
            $table->decimal('total_price', 10, 2);
            $table->enum('currency', ['usd', 'iqd'])->default('usd');
            $table->decimal('currency_value')->default(1);
            $table->string('notes')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
    }
};
