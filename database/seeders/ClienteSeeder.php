<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cliente;

class ClienteSeeder extends Seeder
{
    public function run()
    {
        Cliente::create([
            'nombre' => 'Juan',
            'apellido' => 'Pérez',
            'email' => 'juan.perez@email.com',
            'telefono' => '789456123',
            'direccion' => 'Calle Falsa 123',
            'activo' => true,
        ]);
        Cliente::create([
            'nombre' => 'Ana',
            'apellido' => 'García',
            'email' => 'ana.garcia@email.com',
            'telefono' => '123456789',
            'direccion' => 'Av. Central 456',
            'activo' => true,
        ]);
    }
}
