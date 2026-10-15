<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('descripcion', 255)->nullable();
            $table->decimal('precio', 8, 2);
            $table->string('categoria', 40)->index();
            $table->boolean('disponible')->default(true);
            $table->string('imagen')->nullable();
            $table->timestamps();
        });

        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // carrito | pendiente_pago | pagado | en_preparacion | listo | entregado | cancelado
            $table->string('estado', 20)->default('carrito')->index();
            $table->decimal('total', 10, 2)->default(0);
            $table->string('codigo', 10)->nullable()->unique();   // código de recojo
            $table->string('metodo_pago', 15)->nullable();         // qr | efectivo
            $table->string('razon_social', 100)->nullable();
            $table->string('nit_ci', 20)->nullable();
            $table->unsignedSmallInteger('tiempo_estimado')->nullable(); // minutos
            $table->timestamp('preparacion_inicio')->nullable();
            $table->timestamp('pagado_en')->nullable();
            $table->timestamp('listo_en')->nullable();
            $table->timestamp('entregado_en')->nullable();
            $table->string('cancelado_por', 15)->nullable();       // cliente | personal
            $table->string('motivo_cancelacion', 255)->nullable();
            $table->timestamp('cancelado_en')->nullable();
            $table->boolean('reembolso_pendiente')->default(false);
            $table->timestamp('reembolsado_en')->nullable();
            $table->timestamps();
        });

        Schema::create('detalle_pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nombre', 80);
            $table->decimal('precio_unitario', 8, 2);
            $table->unsignedSmallInteger('cantidad');
            $table->timestamps();
        });

        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained()->cascadeOnDelete();
            $table->string('metodo', 15);                          // qr | efectivo
            $table->string('referencia', 40)->unique();
            $table->decimal('monto', 10, 2);
            // pendiente | por_verificar | recibido | por_cobrar | completado | fallido | cancelado
            $table->string('estado', 20)->default('pendiente')->index();
            $table->timestamp('vence_en')->nullable();
            $table->timestamps();
        });

        Schema::create('verificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pago_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('codigo_hash');
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->timestamp('vence_en');
            $table->boolean('usado')->default(false);
            $table->timestamps();
        });

        Schema::create('facturas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('numero')->unique();
            $table->timestamp('emitida_en');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facturas');
        Schema::dropIfExists('verificaciones');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('detalle_pedidos');
        Schema::dropIfExists('pedidos');
        Schema::dropIfExists('productos');
    }
};
