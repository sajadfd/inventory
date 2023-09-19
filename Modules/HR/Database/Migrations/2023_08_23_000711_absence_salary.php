<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\HR\Entities\Absence;
use Modules\HR\Entities\Salary;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('absence_salary', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Salary::class);
            $table->foreignIdFor(Absence::class);
            $table->decimal('hours',10,2)->nullable();
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
        Schema::dropIfExists('absence_salary');
    }
};
