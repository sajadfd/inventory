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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('category_id');
            $table->string('thumbnail');
            $table->string('image');
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->enum('sale_currency', ['iqd', 'usd'])->default('usd');

            $table->foreignId('brand_id')->nullable();
            $table->foreignId('product_location_id')->nullable();
            $table->string('barcode')->nullable();
            $table->string('main_unit_name')->nullable();
            $table->boolean('is_visible_in_store')->default(true);
            $table->boolean('is_active')->default(true);
            $table->decimal('store', 28, 14)->default(0);
            $table->integer('depletion_alert_at')->default(-1);
            $table->text('notes')->nullable();
            $table->enum('source', ['inside', 'outside'])->default('inside');

            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
