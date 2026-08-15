<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Crea el primer usuario administrador. Cambia el correo y la
     * contraseña aquí abajo (o directamente en la base de datos después).
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@encuestas.pe'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('CambiaEstaClave123!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );
    }
}
