<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrdenItem extends Model
{
    use HasFactory;

    protected $table = 'orden_items';

    protected $fillable = [
        'orden_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal'
    ];

    protected $casts = [
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    // Relaciones
    public function orden()
    {
        return $this->belongsTo(Orden::class);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    
    public function setCantidadAttribute($value)
    {
        $this->attributes['cantidad'] = $value;
        $this->calcularSubtotal();
    }

    public function setPrecioUnitarioAttribute($value)
    {
        $this->attributes['precio_unitario'] = $value;
        $this->calcularSubtotal();
    }

    private function calcularSubtotal()
    {
        if (isset($this->attributes['cantidad']) && isset($this->attributes['precio_unitario'])) {
            $this->attributes['subtotal'] = $this->attributes['cantidad'] * $this->attributes['precio_unitario'];
        }
    }
}
