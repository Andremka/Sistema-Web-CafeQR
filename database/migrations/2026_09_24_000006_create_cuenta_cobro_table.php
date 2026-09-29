<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuenta_cobro', function (Blueprint $table) {
            $table->id();
            $table->string('banco', 80);
            $table->string('titular', 100);
            $table->string('numero', 40);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuenta_cobro');
    }
};
