<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id');
            $table->foreignId('car_type_id');
            $table->foreignId('car_model_id');
            $table->foreignId('color_id');

            $table->string('plate_number')->nullable();
            $table->string('model_year')->nullable();
            $table->string('vin')->unique()->nullable()->comment("رقم الشاصي");
            $table->string('meter_number')->nullable()->comment("رقم العداد");
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
