<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    protected $table = 'marcas';
    protected $primaryKey = 'id_marca';
    public $timestamps = false;

    protected $fillable = [
        'descripcion',
        'pais',
        'fabricante',
        'id_grupo',
        'activo',
        'creado_por',
        'fecha_creado',
        'modificado_por',
        'fecha_mod',
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class, 'id_grupo', 'id_grupo');
    }
}
