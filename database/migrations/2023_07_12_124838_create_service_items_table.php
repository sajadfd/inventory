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
        Schema::create('service_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_list_id');
            $table->foreignId('service_id');
            $table->decimal('count')->default(0);
            $table->decimal('price')->default(0);
            $table->decimal('total_price')->default(0);
            $table->enum('currency', ['usd', 'iqd'])->default('iqd');
            $table->decimal('currency_value')->default(1);
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_items');
    }
};
