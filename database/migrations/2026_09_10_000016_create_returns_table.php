<?php

use App\Models\OrderReturn;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(OrderReturn::STATUS_SOLICITADA);
            $table->string('reason_type');
            $table->string('note')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('received_by')->nullable()->constrained('users');
            $table->timestamp('requested_at');
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'order_id']);
        });

        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignId('order_item_id')->constrained('order_items');
            $table->integer('quantity');
            $table->string('condition')->nullable(); // buena, danada
            $table->timestamps();

            $table->unique(['return_id', 'order_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
    }
};