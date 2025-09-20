<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;
use App\Models\Submenu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        // Dashboard
        $dashboard = Menu::create([
            'name' => 'Dashboard',
            'icon' => 'tabler-smart-home',
            'route' => 'dashboard-inicio',
            'order' => 1,
            'is_active' => true,
        ]);

        // Roles y permisos
        $rolesPermisos = Menu::create([
            'name' => 'Roles y permisos',
            'icon' => 'tabler-settings',
            'order' => 2,
            'is_active' => true,
        ]);

        Submenu::create([
            'menu_id' => $rolesPermisos->id,
            'name' => 'Usuarios',
            'route' => 'apps-usuarios',
            'order' => 1,
            'is_active' => true,
        ]);
        Submenu::create([
            'menu_id' => $rolesPermisos->id,
            'name' => 'Roles',
            'route' => 'apps-roles',
            'order' => 2,
            'is_active' => true,
        ]);
    }
}
