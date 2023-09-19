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
        Schema::create('purchase_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id');
            $table->timestamp('date');
            $table->string('notes')->nullable();
            $table->enum('currency', ['iqd', 'usd'])->default('usd');
            $table->boolean('is_confirmed')->default(false);
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_lists');
    }
};
