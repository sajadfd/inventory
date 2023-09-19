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
        Schema::create('initial_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->unique();
            $table->foreignId('product_unit_id')->nullable();
            $table->decimal('count', 10, 2)->default(0);
            $table->decimal('used', 28,14)->default(0);

            $table->decimal('price', 10, 2)->default(0);
            $table->enum('currency', ['usd', 'iqd'])->default('usd');
            $table->decimal('currency_value', 10, 2)->default(1);

            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('initial_stores');
    }
};
