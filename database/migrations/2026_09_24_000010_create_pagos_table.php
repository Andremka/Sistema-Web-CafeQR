<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Historia de usuario: HU-10 — Pago del pedido mediante código QR.
     */
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->cascadeOnDelete();
            $table->foreignId('metodo_pago_id')->constrained('metodos_pago');
            $table->decimal('monto', 8, 2);
            $table->string('codigo_transaccion', 20)->unique();
            $table->timestamp('fecha_pago');
            $table->string('estado', 20)->default('pagado');
            $table->string('banco_destino', 80);
            $table->string('titular_destino', 100);
            $table->string('numero_destino', 40);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
