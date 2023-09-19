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
        Schema::create('stockholders', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('user_id')->nullable();
            $table->decimal('store_stocks', 10, 2);
            $table->decimal('inventory_stocks', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stockholders');
    }
};
