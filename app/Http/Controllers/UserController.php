<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Listar usuarios
    public function index()
    {
        return response()->json(User::with('roles')->get());
    }

    // Crear usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'fecha_nacimiento' => 'nullable|date',
            'cargo' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);

        $user = User::create($validated);

        // Asignar roles si se proporcionaron
        if (! empty($roles)) {
            $user->roles()->attach($roles);
        }

        return response()->json($user->load('roles'), 201);
    }

    // Editar usuario
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6',
            'fecha_nacimiento' => 'nullable|date',
            'cargo' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'activo' => 'nullable|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $roles = $validated['roles'] ?? null;
        unset($validated['roles']);

        $user->update($validated);

        // Actualizar roles si se proporcionaron
        if ($roles !== null) {
            $user->roles()->sync($roles); // sync reemplaza todos los roles
        }

        return response()->json($user->load('roles'));
    }

    // Desactivar usuario
    public function deactivate($id)
    {
        $user = User::findOrFail($id);
        $user->activo = false;
        $user->save();

        return response()->json(['message' => 'Usuario desactivado']);
    }

    // Activar usuario
    public function activate($id)
    {
        $user = User::findOrFail($id);
        $user->activo = true;
        $user->save();

        return response()->json(['message' => 'Usuario activado']);
    }

    // Obtener perfil del usuario autenticado
    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json($user->load('roles'));
    }

    // Actualizar perfil del usuario autenticado
    public function updateProfile(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'fecha_nacimiento' => 'nullable|date',
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Perfil actualizado exitosamente',
            'user' => $user->fresh()
        ]);
    }

    // Cambiar contraseña del usuario autenticado
    public function changePassword(Request $request)
    {
        $user = $request->user();
        
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        // Verificar la contraseña actual
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta',
                'errors' => ['current_password' => ['La contraseña actual es incorrecta']]
            ], 422);
        }

        // Actualizar la contraseña
        $user->update([
            'password' => Hash::make($validated['new_password'])
        ]);

        return response()->json(['message' => 'Contraseña actualizada exitosamente']);
    }
}
