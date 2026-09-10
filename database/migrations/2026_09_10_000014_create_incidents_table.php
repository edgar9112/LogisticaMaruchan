<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained();
            $table->foreignId('shipment_id')->nullable()->constrained();
            $table->foreignId('user_id')->constrained();
            $table->string('type'); // faltante, sobrante, danado, mercancia_no_localizada, entrega_rechazada...
            $table->text('description');
            $table->string('evidence_path')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};