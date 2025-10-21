<?php

namespace App\Http\Controllers;

use App\Models\SubGrupo;
use Illuminate\Http\Request;

class SubGrupoController extends Controller
{
    public function index()
    {
        // eager load grupo for display
        $items = SubGrupo::with('grupo')->orderBy('id_subgrupo', 'desc')->get();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_grupo' => 'required|exists:grupos,id_grupo',
            'descripcion' => 'required|string|max:255',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = isset($data['activo']) ? (int)$data['activo'] : 1;
        // audit fields required by migration
        $data['creado_por'] = auth()->id() ?? 1;
        $data['fecha_creado'] = now();
        $sub = SubGrupo::create($data);
        return response()->json($sub, 201);
    }

    public function show($id)
    {
        $sub = SubGrupo::with('grupo')->findOrFail($id);
        return response()->json($sub);
    }

    public function update(Request $request, $id)
    {
        $sub = SubGrupo::findOrFail($id);

        $data = $request->validate([
            'id_grupo' => 'sometimes|required|exists:grupos,id_grupo',
            'descripcion' => 'sometimes|required|string|max:255',
            'activo' => 'nullable|boolean',
        ]);

        // Coerce activo if present
        if (array_key_exists('activo', $data)) {
            $data['activo'] = $data['activo'] ? 1 : 0;
        }

        // set modification audit
        $data['modificado_por'] = auth()->id() ?? 1;
        $data['fecha_mod'] = now();

        $sub->update($data);
        return response()->json($sub);
    }

    public function destroy($id)
    {
        $sub = SubGrupo::findOrFail($id);
        $sub->delete();
        return response()->json(null, 204);
    }
}
