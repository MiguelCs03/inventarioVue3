<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; 
class ImagenSistema extends Model
{
    //
    protected $table = 'imagenes_sistema';

    protected $fillable = [
        'uso',
        'ruta',
        'nombre_archivo',
        'es_activa',
        'tamaño_kb',
        'ancho',
        'alto',
    ];

    protected $casts = [
        'es_activa' => 'boolean',
    ];

    // Constantes para los tipos de uso
    const USO_LOGO_HEADER = 'logo_header';
    const USO_LOGIN_ILUSTRACION = 'login_ilustracion';
    const USO_FAVICON = 'favicon';
    const USO_LOADER = 'loader';

    // obtener la imagen acttiva de un tipo especifico
    public static function obtenerActiva($uso)
    {
        return self::where('uso', $uso)
            ->where('es_activa', true)
            ->first();
    }

    //obtener todas las imagenes de un tipo 
    public static function obtenerPorUso($uso)
    {
        return self::where('uso', $uso)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Activar una imagen y desactivar las demás del mismo uso
     */
    public function activar()
    {
        // Desactivar todas las del mismo uso
        self::where('uso', $this->uso)->update(['es_activa' => false]);
        
        // Activar esta
        $this->es_activa = true;
        $this->save();
        
        return $this;
    }

    /**
     * Obtener la URL completa de la imagen
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->ruta);
    }

    /**
     * Eliminar imagen y su archivo
     */
    public function eliminarConArchivo()
    {
        // No permitir eliminar si es la activa
        if ($this->es_activa) {
            return false;
        }

        // Eliminar archivo físico
        if (Storage::exists($this->ruta)) {
            Storage::delete($this->ruta);
        }

        // Eliminar registro
        return $this->delete();
    }
}
