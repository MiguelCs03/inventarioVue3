<?php

namespace App\Http\Controllers;

use App\Models\Unidad;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    public function index()
    {
        $items = Unidad::orderBy('id_unidad', 'desc')->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:150',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = isset($data['activo']) ? (int)$data['activo'] : 1;
        $data['creado_por'] = auth()->id() ?? 1;
        $data['fecha_creado'] = now();

        $unidad = Unidad::create($data);
        return response()->json($unidad, 201);
    }

    public function show($id)
    {
        $unidad = Unidad::findOrFail($id);
        return response()->json($unidad);
    }

    public function update(Request $request, $id)
    {
        $unidad = Unidad::findOrFail($id);

        $data = $request->validate([
            'codigo' => 'nullable|string|max:50',
            'descripcion' => 'nullable|string|max:150',
            'activo' => 'nullable|boolean',
        ]);

        if (array_key_exists('activo', $data)) {
            $data['activo'] = $data['activo'] ? 1 : 0;
        }

        $data['modificado_por'] = auth()->id() ?? 1;
        $data['fecha_mod'] = now();

        $unidad->update($data);
        return response()->json($unidad);
    }

    public function destroy($id)
    {
        $unidad = Unidad::findOrFail($id);
        $unidad->delete();
        return response()->json(null, 204);
    }
}
