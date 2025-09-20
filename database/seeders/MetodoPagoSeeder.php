<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MetodoPago;

class MetodoPagoSeeder extends Seeder
{
    public function run()
    {
        MetodoPago::create([
            'nombre' => 'Efectivo',
            'codigo' => 'EFECTIVO',
            'descripcion' => 'Pago en efectivo',
            'activo' => true,
        ]);
        MetodoPago::create([
            'nombre' => 'QR',
            'codigo' => 'QR',
            'descripcion' => 'Pago mediante código QR',
            'activo' => true,
        ]);
        MetodoPago::create([
            'nombre' => 'Tarjeta',
            'codigo' => 'TARJETA',
            'descripcion' => 'Pago con tarjeta',
            'activo' => true,
        ]);
    }
}
