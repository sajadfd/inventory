<?php

use App\Enums\OrderStatusEnum;
use App\Enums\SaleType;
use App\Models\Car;
use App\Models\Diagnosis;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', SaleType::getAllValues())->default(SaleType::StoreSale->value);
            $table->string('notes')->nullable();
            $table->foreignIdFor(User::class);
            $table->foreignIdFor(Car::class)->nullable();
            $table->foreignIdFor(Diagnosis::class)->nullable();
            $table->foreignId('created_by');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
