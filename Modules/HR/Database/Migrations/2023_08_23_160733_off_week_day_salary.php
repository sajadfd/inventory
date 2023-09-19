<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\HR\Entities\OffWeekDay;
use Modules\HR\Entities\Salary;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('off_week_day_salary', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Salary::class);
            $table->foreignIdFor(OffWeekDay::class);
            $table->decimal('days', 10, 2)->nullable();
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
        Schema::dropIfExists('off_week_day_salary');
    }
};
