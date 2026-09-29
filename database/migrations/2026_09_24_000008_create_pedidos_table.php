<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('estado_pedido_id')->constrained('estados_pedido');
            $table->unsignedInteger('numero_pedido')->unique();
            $table->unsignedSmallInteger('codigo_recojo')->nullable();
            $table->decimal('total', 8, 2);
            $table->boolean('listo')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
