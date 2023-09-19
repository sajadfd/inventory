<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::create('stockholder_withdraws', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stockholder_id');
            $table->decimal('price', 10, 2);
            $table->string('currency');
            $table->decimal('currency_value', 10, 2);
            $table->timestamp('date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stockholder_withdraws');
    }
};
