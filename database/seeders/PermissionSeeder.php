<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['name' => 'Dashboard', 'subject' => 'dashboard-inicio'],
            ['name' => 'Usuarios', 'subject' => 'apps-usuarios'],
            ['name' => 'Roles', 'subject' => 'apps-roles'],
            ['name' => 'Proveedores', 'subject' => 'proveedores'],
            ['name' => 'Compras', 'subject' => 'compras'],
            // Agrega más secciones según se vaya creando páginas
        ];

        foreach ($sections as $sec) {
            Permission::updateOrCreate(
                ['subject' => $sec['subject'], 'action' => 'read'],
                [
                    'name' => $sec['name'],
                    'action' => 'read',
                    'subject' => $sec['subject'],
                    'description' => 'Acceso a la sección '.$sec['name'],
                ]
            );
        }

        // Asignar todos los permisos al rol admin si existe
        $adminRole = Role::where('nombre', 'admin')->first();
        if ($adminRole) {
            $permissionIds = Permission::pluck('id')->all();
            $adminRole->permissions()->sync($permissionIds);
        }
    }
}
