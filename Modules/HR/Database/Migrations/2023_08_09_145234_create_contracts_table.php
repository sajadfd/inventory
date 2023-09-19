<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\HR\Enums\SalaryCurrencyEnum;
use Modules\HR\Enums\SalaryTypeEnum;
use Modules\HR\Enums\TrackByEnum;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->ForeignId('employer_id')->constrained('employers')->cascadeOnDelete();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->enum('salary_type', SalaryTypeEnum::getAllValues())->nullable();
            $table->decimal('salary_price', 10, 2)->nullable();
            $table->enum('salary_currency', SalaryCurrencyEnum::getAllValues())->default(SalaryCurrencyEnum::Iqd->value);
            $table->decimal('day_work_hours', 3, 1)->nullable();
            $table->decimal('day_work_start_hour', 3, 1)->nullable();
            $table->enum('track_by', TrackByEnum::getAllValues())->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('contracts');
    }
};
