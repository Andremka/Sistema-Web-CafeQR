<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@cafeqr.edu'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'rol' => 'administrador',
            ]
        );
    }
}
