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
        Schema::create('apk_versions', function (Blueprint $table) {
            $table->uuid('uuid');
            $table->decimal('version', 20, 12);
            $table->string('file_name');
            $table->string('file_path');
            $table->string('channel');
            $table->string('platform');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apk_versions');
    }
};
