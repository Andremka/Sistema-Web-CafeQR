<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Cupones de descuento (aporte de la rama main del equipo). Solo agrega; no borra datos. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->decimal('monto', 6, 2);
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->string('cupon', 20)->nullable()->after('total');
            $table->decimal('descuento', 8, 2)->default(0)->after('cupon');
        });
    }

    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropColumn(['cupon', 'descuento']);
        });
        Schema::dropIfExists('cupones');
    }
};
