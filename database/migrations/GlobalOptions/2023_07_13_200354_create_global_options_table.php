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
        Schema::create('global_options', function (Blueprint $table) {
            $table->string('name')->unique()->primary();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\Artisan::call('sync:options');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_options');
    }
};
