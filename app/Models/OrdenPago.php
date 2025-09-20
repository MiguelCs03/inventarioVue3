<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrdenPago extends Model
{
    protected $table = 'orden_pagos';
    
    protected $fillable = [
        'orden_id',
        'metodo_pago_id',
        'monto',
        'detalle'
    ];

    protected $casts = [
        'monto' => 'decimal:2'
    ];

    // Relaciones
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class);
    }
}
