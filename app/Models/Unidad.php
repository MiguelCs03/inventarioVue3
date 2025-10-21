<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unidad extends Model
{
    protected $table = 'unidades';
    protected $primaryKey = 'id_unidad';
    public $timestamps = false;

    protected $fillable = [
        'codigo',
        'descripcion',
        'activo',
        'creado_por',
        'fecha_creado',
        'modificado_por',
        'fecha_mod',
    ];
}
