<?php

use App\Enums\UserType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable()->unique();
            $table->string('password');
            $table->enum('type', UserType::getAllValues());//->default(UserType::Other->value);
            $table->string('code', 3)->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable()->unique();
            $table->json('options')->default("{}");
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->foreignId('created_by')->nullable();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
