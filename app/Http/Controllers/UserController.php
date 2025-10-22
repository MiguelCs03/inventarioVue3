<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Listar usuarios
    public function index()
    {
        $users = User::with('roles')->get();
        
        // Transformar para incluir avatar_url
        $users = $users->map(function ($user) {
            $userData = $user->toArray();
            if (!empty($user->avatar)) {
                $userData['avatar_url'] = asset('storage/' . $user->avatar);
            }
            return $userData;
        });
        
        return response()->json($users);
    }

    // Crear usuario
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'numero' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'fecha_nacimiento' => 'nullable|date',
            'cargo' => 'nullable|string|max:255',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $roles = $validated['roles'] ?? [];
        unset($validated['roles']);

        // Manejar subida de avatar opcional
        if ($request->hasFile('avatar')) {
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($validated);

        // Asignar roles si se proporcionaron
        if (! empty($roles)) {
            $user->roles()->attach($roles);
        }

        // Responder incluyendo URL pública del avatar si existe
        $user->load('roles');
        $response = $user->toArray();
        if (!empty($user->avatar)) {
            $response['avatar_url'] = asset('storage/' . $user->avatar);
        }
        return response()->json($response, 201);
    }

    // Editar usuario
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => 'sometimes|string|max:255|unique:users,username,'.$id,
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'numero' => 'nullable|string|max:20',
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
    // subir imagen al perfil 
    public function uploadAvatar(Request $request)
    {
        // Debug: registrar información de la sesión y autenticación
        \Log::info('Upload avatar attempt', [
            'session_id' => $request->session()->getId(),
            'has_session' => $request->hasSession(),
            'auth_check' => Auth::check(),
            'auth_id' => Auth::id(),
        ]);

        $user = Auth::user(); 
        
        if (!$user) {
            // Si por alguna razón el usuario no está autenticado, devuelve un error 401
            \Log::warning('Avatar upload failed: user not authenticated');
            return response()->json(['message' => 'No autorizado. Por favor, inicie sesión.'], 401);
        }

        $validated = $request->validate([
            'avatar' => 'required|image|max:2048', 
        ]);

        $path = $request->file('avatar')->store('avatars', 'public');

        // Eliminar imagen anterior si existe
        if ($user->avatar && \Storage::disk('public')->exists($user->avatar)) {
            \Storage::disk('public')->delete($user->avatar);
        }
        
        $user->avatar = $path;
        $user->save();

        return response()->json([
            'message' => 'Avatar subido exitosamente',
            'avatar_url' => asset('storage/' . $path),
        ]);
    }
}
