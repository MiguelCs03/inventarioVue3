<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Orden extends Model
{
    use HasFactory;

    protected $table = 'ordenes';

    protected $fillable = [
        'cliente_id',
        'fecha',
        'estado',
        'total',
        'observaciones',
        'fecha_confirmacion',
        'total_pagado'
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
        'total_pagado' => 'decimal:2',
        'fecha_confirmacion' => 'datetime'
    ];

    // Relaciones
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function items()
    {
        return $this->hasMany(OrdenItem::class);
    }

    public function pagos()
    {
        return $this->hasMany(OrdenPago::class);
    }

    // Método para calcular el total
    public function calcularTotal()
    {
        $this->total = $this->items->sum('subtotal');
        $this->save();
        return $this->total;
    }
}
