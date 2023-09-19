<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('off_week_days', function (Blueprint $table) {
            $table->id();
            $table->ForeignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->boolean('consider_as_attendance')->default(false);
            $table->integer('day');
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('off_week_days');
    }
};
