<?php

namespace App\Http\Controllers;

use App\Models\ImagenSistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

class ImagenSistemaController extends Controller
{
    // Configuración de límites y validaciones por tipo
    private $configuraciones = [
        'logo_header' => [
            'max_imagenes' => 10,
            'formatos' => ['png', 'jpg', 'jpeg', 'svg'],
            'tamaño_max_mb' => 2,
            'ancho_recomendado' => 200,
            'alto_recomendado' => 60,
        ],
        'login_ilustracion' => [
            'max_imagenes' => 10,
            'formatos' => ['png', 'jpg', 'jpeg', 'webp'],
            'tamaño_max_mb' => 5,
            'ancho_recomendado' => 1200,
            'alto_recomendado' => 900,
        ],
        'favicon' => [
            'max_imagenes' => 10,
            // Permitimos formatos raster comunes; convertimos a PNG 32x32 al subir
            'formatos' => ['png', 'ico', 'svg', 'jpg', 'jpeg', 'webp'],
            'tamaño_max_mb' => 1,
            'ancho_recomendado' => 32,
            'alto_recomendado' => 32,
        ],
        'loader' => [
            'max_imagenes' => 10,
            'formatos' => ['gif', 'svg', 'webp', 'png'],
            'tamaño_max_mb' => 1,
            'ancho_recomendado' => 100,
            'alto_recomendado' => 100,
        ],
    ];

    /**
     * Listar todas las imágenes de un tipo
     */
    public function index($uso)
    {
        if (!isset($this->configuraciones[$uso])) {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de imagen no válido'
            ], 400);
        }

        $imagenes = ImagenSistema::obtenerPorUso($uso);
        
        return response()->json([
            'success' => true,
            'data' => $imagenes,
            'configuracion' => $this->configuraciones[$uso]
        ]);
    }

    /**
     * Obtener solo la imagen activa de un tipo
     */
    public function activa($uso)
    {
        $imagen = ImagenSistema::obtenerActiva($uso);
        
        return response()->json([
            'success' => true,
            'data' => $imagen
        ]);
    }

    /**
     * Subir nueva imagen
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'uso' => 'required|in:logo_header,login_ilustracion,favicon,loader',
            'imagen' => 'required|image|mimes:' . implode(',', $this->configuraciones[$request->uso]['formatos'] ?? []) . '|max:' . ($this->configuraciones[$request->uso]['tamaño_max_mb'] * 1024),
        ], [
            'uso.required' => 'El tipo de imagen es requerido',
            'uso.in' => 'Tipo de imagen no válido',
            'imagen.required' => 'Debe seleccionar una imagen',
            'imagen.image' => 'El archivo debe ser una imagen',
            'imagen.mimes' => 'Formato de imagen no permitido',
            'imagen.max' => 'La imagen supera el tamaño máximo permitido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $uso = $request->uso;

        // Verificar límite de imágenes
        $count = ImagenSistema::where('uso', $uso)->count();
        if ($count >= $this->configuraciones[$uso]['max_imagenes']) {
            return response()->json([
                'success' => false,
                'message' => 'Has alcanzado el límite de ' . $this->configuraciones[$uso]['max_imagenes'] . ' imágenes para este tipo'
            ], 400);
        }

        $imagen = $request->file('imagen');
        $nombreOriginal = $imagen->getClientOriginalName();
        
        // Generar nombre único
        $nombreArchivo = time() . '_' . str_replace(' ', '_', $nombreOriginal);
        
        // Determinar carpeta según el uso
        $carpeta = match($uso) {
            'logo_header' => 'branding/logos',
            'login_ilustracion' => 'branding/login',
            'favicon' => 'branding/favicon',
            'loader' => 'branding/loader',
            default => 'branding/otros'
        };

        // Guardar archivo inicialmente
        $ruta = $imagen->storeAs($carpeta, $nombreArchivo, 'public');

        // Ruta completa en disco
        $rutaCompleta = storage_path('app/public/' . $ruta);

        // Redimensionado por defecto para ciertos usos (si Intervention está disponible)
        $tamanosPorDefecto = [
            'logo_header' => [30, 30], // tamaño pequeño por defecto para logo en nav
            'favicon' => [32, 32],
            'loader' => [100, 100],
            'login_ilustracion' => null,
        ];

        $ext = strtolower(pathinfo($rutaCompleta, PATHINFO_EXTENSION));
        $esRaster = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);

        // Si existe un tamaño por defecto y es una imagen raster, intentamos redimensionar
        $targetSize = $tamanosPorDefecto[$uso] ?? null;
        if ($targetSize && $esRaster) {
            list($targetW, $targetH) = $targetSize;
            if (class_exists(Image::class)) {
                try {
                    $img = Image::make($rutaCompleta)->fit($targetW, $targetH);
                    // Sobrescribimos el archivo existente
                    $img->save($rutaCompleta, 90);
                    // Actualizamos dimensiones para registrar en BD
                    $ancho = $targetW;
                    $alto = $targetH;
                } catch (\Exception $e) {
                    \Log::warning('Error al redimensionar imagen para uso ' . $uso . ': ' . $e->getMessage());
                }
            } else {
                \Log::warning('Intervention Image no está instalado; no se redimensionó la imagen para ' . $uso);
            }
        }

        // Obtener dimensiones usando getimagesize (para SVG puede devolver false)
        $dimensiones = @getimagesize($rutaCompleta);
        $ancho = $dimensiones ? $dimensiones[0] : null;
        $alto = $dimensiones ? $dimensiones[1] : null;

        // Crear registro en BD con la ruta final
        $imagenSistema = ImagenSistema::create([
            'uso' => $uso,
            'ruta' => $ruta,
            'nombre_archivo' => $nombreOriginal,
            'es_activa' => false,
            'tamaño_kb' => round($imagen->getSize() / 1024, 2),
            'ancho' => $ancho,
            'alto' => $alto,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Imagen subida correctamente',
            'data' => $imagenSistema
        ], 201);
    }

    /**
     * Activar una imagen
     */
    public function activar($id)
    {
        $imagen = ImagenSistema::find($id);
        
        if (!$imagen) {
            return response()->json([
                'success' => false,
                'message' => 'Imagen no encontrada'
            ], 404);
        }

        $imagen->activar();

        return response()->json([
            'success' => true,
            'message' => 'Imagen activada correctamente',
            'data' => $imagen
        ]);
    }

    /**
     * Eliminar una imagen
     */
    public function destroy($id)
    {
        $imagen = ImagenSistema::find($id);
        
        if (!$imagen) {
            return response()->json([
                'success' => false,
                'message' => 'Imagen no encontrada'
            ], 404);
        }

        if ($imagen->es_activa) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes eliminar la imagen activa. Primero activa otra imagen.'
            ], 400);
        }

        $resultado = $imagen->eliminarConArchivo();

        if ($resultado) {
            return response()->json([
                'success' => true,
                'message' => 'Imagen eliminada correctamente'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar la imagen'
        ], 500);
    }

    /**
     * Obtener todas las imágenes activas (para uso en frontend)
     */
    public function todasActivas()
    {
        $imagenes = [
            'logo_header' => ImagenSistema::obtenerActiva('logo_header'),
            'login_ilustracion' => ImagenSistema::obtenerActiva('login_ilustracion'),
            'favicon' => ImagenSistema::obtenerActiva('favicon'),
            'loader' => ImagenSistema::obtenerActiva('loader'),
        ];

        return response()->json([
            'success' => true,
            'data' => $imagenes
        ]);
    }
}