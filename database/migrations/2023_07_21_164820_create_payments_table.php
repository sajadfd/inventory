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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id');
            $table->uuid('code');
            $table->decimal('price', 10, 2);
            $table->enum('currency', ['iqd', 'usd'])->default('iqd');
            $table->decimal('currency_value')->default(1);
            $table->string('notes')->nullable();
            $table->timestamp('payed_at');
            $table->foreignId('received_by');
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
