<?php

use App\Enums\CurrencyEnum;
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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->string('code', 255);
            $table->decimal('price', 10, 2);
            $table->timestamp('due_date');
             $table->foreignId('penalty_id')->nullable();
            $table->foreignId('loan_id');
            $table->integer('sequence_number')->nullable();
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
        Schema::dropIfExists('installments');
    }
};
