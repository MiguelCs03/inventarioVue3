<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    // Listar todos los permisos
    public function index()
    {
        return response()->json(Permission::all());
    }

    // Listar permisos asignados a un rol
    public function byRole($roleId)
    {
        $role = Role::with('permissions')->findOrFail($roleId);
        return response()->json([
            'role' => $role->only(['id','nombre']),
            'permissions' => $role->permissions->pluck('id'),
        ]);
    }

    // Sincronizar permisos de un rol
    public function syncForRole(Request $request, $roleId)
    {
        $data = $request->validate([
            'permission_ids' => 'array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::findOrFail($roleId);
        $role->permissions()->sync($data['permission_ids'] ?? []);

        return response()->json(['message' => 'Permisos del rol actualizados correctamente']);
    }
}
