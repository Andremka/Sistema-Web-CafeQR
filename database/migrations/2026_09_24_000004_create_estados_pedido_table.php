<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estados_pedido', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 30)->unique();
            $table->string('descripcion', 120)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estados_pedido');
    }
};
