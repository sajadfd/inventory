<?php

use App\Enums\ExpenseSource;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->enum('source', ExpenseSource::getAllValues())->default(ExpenseSource::StoreExpense->value);
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('currency', ['usd', 'iqd'])->default('iqd');
            $table->decimal('currency_value', 10, 2)->default(1);
            $table->timestamp('date');
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
