<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $table = 'grupos';
    protected $primaryKey = 'id_grupo';
    public $timestamps = false; 

    protected $fillable = [
        'descripcion',
        'activo',
        'creado_por',
        'fecha_creado',
        'modificado_por',
        'fecha_mod',
    ];
}
