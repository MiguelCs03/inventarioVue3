<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    public function run()
    {
        Producto::create([
            'nombre' => 'Laptop HP',
            'descripcion' => 'Laptop HP 15 pulgadas, 8GB RAM, 256GB SSD',
            'precio' => 4500.00,
            'stock' => 10,
            'activo' => true,
        ]);
        Producto::create([
            'nombre' => 'Mouse Logitech',
            'descripcion' => 'Mouse inalámbrico Logitech M185',
            'precio' => 120.00,
            'stock' => 50,
            'activo' => true,
        ]);
        Producto::create([
            'nombre' => 'Monitor Samsung',
            'descripcion' => 'Monitor Samsung 24" Full HD',
            'precio' => 950.00,
            'stock' => 20,
            'activo' => true,
        ]);
    }
}
