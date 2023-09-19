<?php

use App\Enums\ProductTransactionEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('product_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id');
            $table->foreignId('source_product_unit_id')->nullable();
            $table->foreignId('target_product_unit_id')->nullable();
            $table->decimal('count', 10, 2)->default(0);
            $table->string('sourceable_type')->nullable();
            $table->string('sourceable_id')->nullable();
            $table->string('targetable_type')->nullable();
            $table->string('targetable_id')->nullable();
            $table->enum('type', ProductTransactionEnum::getAllValues())->default(ProductTransactionEnum::Other->value);
            $table->string('description')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_transactions');
    }
};
