<?php

use Database\Seeders\AdminSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Cambia la cuenta del administrador a la nueva (admi7@est.univalle.edu / Admi1234@).
 * Si ya existía un administrador con otro correo, se ACTUALIZA ese mismo usuario
 * (no se crea un segundo admin y no se pierde nada).
 */
return new class extends Migration
{
    public function up(): void
    {
        (new AdminSeeder)->run();
    }

    public function down(): void
    {
        //
    }
};
