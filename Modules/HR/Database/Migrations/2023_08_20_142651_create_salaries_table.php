<?php

use App\Enums\CurrencyEnum;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\HR\Enums\SalaryTypeEnum;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained();
            $table->decimal('price', 10, 2);
            $table->enum('currency', CurrencyEnum::getAllValues())->default(CurrencyEnum::Iqd->value);
            $table->decimal('currency_value', 10, 2)->default(1);
            $table->enum('type', SalaryTypeEnum::getAllValues())->default(SalaryTypeEnum::ByMonth->value);
            $table->decimal('worked_days', 10, 2)->nullable();
            $table->decimal('worked_hours', 10, 2)->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('payed_at')->nullable();
            $table->boolean('is_payed')->default(false);
            $table->text('notes')->nullable();
            $table->string('code')->nullable();
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
        Schema::dropIfExists('salaries');
    }
};
