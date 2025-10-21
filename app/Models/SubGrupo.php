<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubGrupo extends Model
{
    protected $table = 'sub_grupos';
    protected $primaryKey = 'id_subgrupo';
    public $timestamps = false;

    protected $fillable = [
        'id_grupo',
        'descripcion',
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

