<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Proveedor;

class ProveedorSeeder extends Seeder
{
    public function run(): void
    {
        Proveedor::insert([
            [
                'nombre' => 'Distribuidora Central',
                'contacto' => 'Carlos López',
                'email' => 'central@proveedores.com',
                'telefono' => '555-1234',
                'activo' => true,
            ],
            [
                'nombre' => 'Alimentos del Norte',
                'contacto' => 'María Torres',
                'email' => 'norte@proveedores.com',
                'telefono' => '555-5678',
                'activo' => true,
            ],
            [
                'nombre' => 'Farmacia Global',
                'contacto' => 'Juan Pérez',
                'email' => 'global@proveedores.com',
                'telefono' => '555-9012',
                'activo' => true,
            ],
        ]);
    }
}
