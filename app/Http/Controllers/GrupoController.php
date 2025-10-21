<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GrupoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $grupos = Grupo::orderBy('id_grupo', 'desc')->get();
            return response()->json($grupos);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al obtener grupos'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'descripcion' => 'required|string|max:200',
                'activo' => 'boolean',
            ]);
            
            $grupo = new Grupo($validated);
            $grupo->creado_por = 1; // Cambia por auth()->id() cuando tengas auth
            $grupo->fecha_creado = now();
            $grupo->save();
            
            return response()->json([
                'message' => 'Grupo creado exitosamente',
                'data' => $grupo
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear grupo'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            return response()->json($grupo);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Grupo no encontrado'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $validated = $request->validate([
                'descripcion' => 'sometimes|required|string|max:200',
                'activo' => 'boolean',
            ]);
            
            $grupo->fill($validated);
            $grupo->modificado_por = 1; // Cambia por auth()->id() cuando tengas auth
            $grupo->fecha_mod = now();
            $grupo->save();
            
            return response()->json([
                'message' => 'Grupo actualizado exitosamente',
                'data' => $grupo
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al actualizar grupo'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $grupo = Grupo::findOrFail($id);
            $grupo->delete();
            return response()->json(['message' => 'Grupo eliminado exitosamente'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar grupo'], 500);
        }
    }
}
