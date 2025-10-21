<?php

namespace App\Http\Controllers;

use App\Models\Marca;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    public function index()
    {
        $items = Marca::with('grupo')->orderBy('id_marca', 'desc')->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'descripcion' => 'nullable|string|max:200',
            'pais' => 'nullable|string|max:100',
            'fabricante' => 'nullable|string|max:150',
            'id_grupo' => 'nullable|exists:grupos,id_grupo',
            'activo' => 'nullable|boolean',
        ]);

        $data['activo'] = isset($data['activo']) ? (int)$data['activo'] : 1;
        $data['creado_por'] = auth()->id() ?? 1;
        $data['fecha_creado'] = now();

        $marca = Marca::create($data);
        return response()->json($marca, 201);
    }

    public function show($id)
    {
        $marca = Marca::with('grupo')->findOrFail($id);
        return response()->json($marca);
    }

    public function update(Request $request, $id)
    {
        $marca = Marca::findOrFail($id);

        $data = $request->validate([
            'descripcion' => 'nullable|string|max:200',
            'pais' => 'nullable|string|max:100',
            'fabricante' => 'nullable|string|max:150',
            'id_grupo' => 'nullable|exists:grupos,id_grupo',
            'activo' => 'nullable|boolean',
        ]);

        if (array_key_exists('activo', $data)) {
            $data['activo'] = $data['activo'] ? 1 : 0;
        }

        $data['modificado_por'] = auth()->id() ?? 1;
        $data['fecha_mod'] = now();

        $marca->update($data);
        return response()->json($marca);
    }

    public function destroy($id)
    {
        $marca = Marca::findOrFail($id);
        $marca->delete();
        return response()->json(null, 204);
    }
}
