<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\HR\Entities\Attendance;
use Modules\HR\Entities\Salary;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('attendance_salary', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Salary::class);
            $table->foreignIdFor(Attendance::class);
            $table->decimal('hours', 10, 2)->nullable();
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
        Schema::dropIfExists('attendance_salary');
    }
};
