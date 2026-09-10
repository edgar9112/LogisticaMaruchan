<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->string('shipment_number')->unique();
            $table->foreignId('origin_warehouse_id')->constrained('warehouses');
            $table->foreignId('destination_store_id')->constrained('stores');
            $table->foreignId('vehicle_id')->nullable()->constrained();
            $table->string('driver_name')->nullable();
            $table->string('status')->default('PREPARADO'); // PREPARADO, CARGADO, EN_TRANSITO, ENTREGADO, CERRADO
            $table->text('notes')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('departed_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};