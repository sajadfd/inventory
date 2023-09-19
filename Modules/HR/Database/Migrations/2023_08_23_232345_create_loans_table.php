<?php

use App\Enums\CurrencyEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\HR\Enums\PaymentMethodEnum;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id');
            $table->decimal('price', 10, 2);
            $table->decimal('profit_percent', 10, 2);
            $table->enum('currency', CurrencyEnum::getAllValues())->default(CurrencyEnum::Iqd->value);
            $table->decimal('currency_value', 10, 2);
            $table->decimal('duration', 10, 1);
            $table->enum('payment_method', PaymentMethodEnum::getAllValues())->default(PaymentMethodEnum::Monthly->value);
            $table->timestamp('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loans');
    }
};
