<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('presentation_id')->constrained();
            $table->integer('quantity_requested');
            $table->integer('quantity_received')->nullable();
            $table->integer('quantity_prepared')->default(0);
            $table->timestamps();

            $table->unique(['order_id', 'presentation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};