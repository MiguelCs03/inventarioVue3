<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ConfiguracionController extends Controller
{
    /**
     * Obtener la configuración actual
     */
    public function index()
    {
        $configuracion = Configuracion::obtener();
        
        return response()->json([
            'success' => true,
            'data' => $configuracion
        ]);
    }

    /**
     * Actualizar la configuración
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre_empresa' => 'required|string|max:255',
            'titulo_navegador' => 'required|string|max:255',
        ], [
            'nombre_empresa.required' => 'El nombre de la empresa es requerido',
            'titulo_navegador.required' => 'El título del navegador es requerido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $configuracion = Configuracion::actualizarConfiguracion($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada correctamente',
            'data' => $configuracion
        ]);
    }

    /**
     * Obtener configuración pública (sin autenticación)
     * Para usar en login y páginas públicas
     */
    public function publica()
    {
        $configuracion = Configuracion::obtener();
        
        return response()->json([
            'success' => true,
            'data' => $configuracion
        ]);
    }
}