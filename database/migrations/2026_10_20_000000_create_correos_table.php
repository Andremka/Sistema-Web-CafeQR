<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Registro de cada correo que envía CafeQR (para ver cuáles fallaron y reenviarlos). Solo agrega. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('correos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('para', 150)->index();
            $table->string('asunto', 200);
            $table->string('tipo', 40);                 // bienvenida | codigo | pago-confirmado | ...
            $table->string('estado', 15)->index();      // enviado | fallido | prueba
            $table->text('error')->nullable();
            $table->unsignedTinyInteger('intentos')->default(1);
            $table->longText('html')->nullable();       // para poder reenviarlo igual
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('correos');
    }
};
