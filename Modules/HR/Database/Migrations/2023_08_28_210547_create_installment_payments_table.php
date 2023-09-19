<?php

use App\Enums\CurrencyEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('installment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('installment_id')->constrained();
            $table->decimal('price', 10, 2);
            $table->enum('currency', CurrencyEnum::getAllValues())->default(CurrencyEnum::Iqd->value);
            $table->decimal('currency_value', 10, 2)->default(1);
            $table->timestamp('payed_at');
            $table->string('code');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installment_payments');
    }
};
