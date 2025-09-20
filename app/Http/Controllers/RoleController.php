<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;

class RoleController extends Controller
{
    // Listar roles
    public function index()
    {
        return response()->json(Role::all());
    }

    // Crear rol
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'activo' => 'required|boolean',
        ]);

        $role = Role::create($validated);
        return response()->json($role, 201);
    }

    // Editar rol
    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);
        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'activo' => 'sometimes|boolean',
        ]);

        $role->update($validated);
        return response()->json($role);
    }

    // Deshabilitar rol
    public function deactivate($id)
    {
        $role = Role::findOrFail($id);
        $role->activo = false;
        $role->save();
        return response()->json(['message' => 'Rol deshabilitado']);
    }

    // Habilitar rol
    public function activate($id)
    {
        $role = Role::findOrFail($id);
        $role->activo = true;
        $role->save();
        return response()->json(['message' => 'Rol habilitado']);
    }
}
