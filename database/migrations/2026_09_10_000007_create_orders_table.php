<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique(); // folio
            $table->foreignId('store_id')->constrained();
            $table->foreignId('user_id')->constrained(); // quien lo creó (ventas)
            $table->foreignId('warehouse_id')->nullable()->constrained();
            $table->string('status')->default('CREADO');
            $table->text('notes')->nullable();
            $table->timestamp('ordered_at');
            $table->timestamp('received_at')->nullable();
            $table->timestamp('prepared_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('store_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};