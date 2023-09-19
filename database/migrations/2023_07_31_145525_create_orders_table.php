<?php

use App\Enums\SaleType;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Diagnosis;
use App\Models\Driver;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('type', SaleType::getAllValues())->default(SaleType::StoreSale->value);
            $table->string('notes')->nullable();
            $table->enum('currency', ['iqd', 'usd'])->default('iqd');
            $table->string('status');
            $table->timestamp('appointment')->nullable();
            $table->string('end_latitude')->nullable();
            $table->string('end_longitude')->nullable();
            $table->string('end_address')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->foreignId('created_by');
            $table->foreignIdFor(Diagnosis::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Car::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Driver::class)->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
