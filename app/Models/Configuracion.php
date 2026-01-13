<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Configuracion extends Model
{
    //
    protected $table = 'configuracion';

    protected $fillable = [
        'nombre_empresa',
        'titulo_navegador',
    ];
// metodo que obtiene la configuracion guardada
    protected static function obtener()
    {
        return self::first();
    }
//metodo para actualizar la configuracion
    protected static function actualizarConfiguracion($data)
    {
        $config = self::first();
        if ($config) {
            $config->update($data);
            return $config;
        }
        return null; 
    }

}
