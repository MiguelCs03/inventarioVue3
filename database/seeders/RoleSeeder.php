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

        // Crear rol usuario regular
        $userRole = Role::create([
            'nombre' => 'usuario',
        ]);

        // Crear usuario admin con contraseña encriptada
        $adminUser = User::create([
            'name' => 'Administrador',
            'email' => 'admin@demo.com',
            'password' => bcrypt('admin123'), // Contraseña encriptada
        ]);
        
        // Asignar rol admin al usuario administrador
        $adminUser->roles()->attach($adminRole->id);

        // Encontrar el usuario de prueba y asignarle un rol
        $testUser = User::where('email', 'test@example.com')->first();
        if ($testUser) {
            $testUser->roles()->attach($userRole->id);
        }
    }
}
