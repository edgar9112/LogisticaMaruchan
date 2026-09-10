<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presentations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('Sopa Maruchan');
            $table->string('presentation_type'); // vaso, bolsa, caja
            $table->string('flavor')->nullable(); // sabor
            $table->integer('pieces_per_box')->nullable();
            $table->string('sku')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presentations');
    }
};