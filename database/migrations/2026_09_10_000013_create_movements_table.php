<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('trackable'); // order, package, shipment
            $table->foreignId('user_id')->nullable()->constrained();
            $table->string('state'); // CREADO, RECIBIDO, EN_TRANSITO...
            $table->string('action'); // creado, recibido en almacen...
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};