<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // Crear rol admin
        $adminRole = Role::create([
            'nombre' => 'admin',
        ]);

        // Crear usuario admin con contraseña encriptada
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@demo.com',
            'password' => bcrypt('admin123'), // Contraseña encriptada
            'role_id' => $adminRole->id,
        ]);
    }
}
