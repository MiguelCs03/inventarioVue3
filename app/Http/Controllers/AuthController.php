<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($validated)) {
            $user = Auth::user();

            // Crear un token simple 
            $token = bin2hex(random_bytes(32));

            // Obtener permisos por roles y calcular secciones permitidas
            $userPermissions = [];
            $allowedSections = [];

            $user->loadMissing('roles.permissions');
            $isAdmin = false;

            // Verificar si es admin (múltiples variantes posibles)
            $adminRoles = ['admin', 'administrador', 'administrator'];
            foreach ($user->roles as $role) {
                if (in_array(strtolower($role->nombre), $adminRoles)) {
                    $isAdmin = true;
                    break;
                }
            }

            // Si es admin, darle acceso completo
            if ($isAdmin) {
                $allowedSections = [
                    'dashboard-inicio',
                    'apps-usuarios',
                    'apps-roles',
                    'compras',
                    'productos',
                    'clientes',
                    'proveedores',
                    'ordenes',
                    'reportes',
                ];
                $userAbilityRules = [['action' => 'manage', 'subject' => 'all']];
            } else {
                // Para usuarios no-admin, calcular permisos específicos
                foreach ($user->roles as $role) {
                    foreach ($role->permissions as $permission) {
                        $userPermissions[] = $permission->name;

                        // Usar subject como sección permitida; fallback al name si no hay subject
                        $section = $permission->subject ?: $permission->name;
                        if (! empty($section)) {
                            $allowedSections[] = trim(strtolower($section));
                        }
                    }
                }

                // Generar reglas de habilidad para CASL
                $userAbilityRules = [];
                // Asegurar dashboard-inicio siempre accesible para usuarios autenticados
                $allowedSections[] = 'perfil';
                $normalizedSections = array_values(array_unique(array_map(function ($s) {
                    return trim(strtolower($s));
                }, $allowedSections)));
                foreach ($normalizedSections as $section) {
                    $userAbilityRules[] = [
                        'action' => 'read',
                        'subject' => trim($section),
                    ];
                }
            }
              

            // Log debug
            \Log::info('Login payload', [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('nombre')->toArray(),
                ],
                'isAdmin' => $isAdmin,
                'allowedSections' => array_values(array_unique($allowedSections)),
                'abilityRules' => $userAbilityRules,
            ]);

            return response()->json([
                'accessToken' => $token,
                'userData' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'fullName' => $user->name, // Alias para compatibilidad frontend
                    'username' => $user->name, // Alias para compatibilidad frontend
                    'role' => $user->roles->first()?->nombre ?? 'Usuario', // Rol principal para mostrar
                    'roles' => $user->roles->pluck('nombre')->toArray(),
                    'permissions' => array_unique($userPermissions),
                    'allowedSections' => array_unique($allowedSections),
                ],
                'userAbilityRules' => $userAbilityRules,
                'homeRoute' => '/dashboard-inicio',
            ]);
        }

        return response()->json([
            'errors' => [
                'email' => ['Credenciales incorrectas'],
            ],
        ], 422);
    }

    private function homeRouteForRole($roleName)
    {
        if (! $roleName) {
            return '/';
        }
        switch (strtolower($roleName)) {
            case 'admin':
                return '/dashboard-inicio';
            case 'empleado':
            case 'employee':
            case 'trabajador':
                return '/employee-home';
            default:
                return '/dashboard-inicio';
        }
    }

    // Logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }

    // Update Password
    public function updatePassword(Request $request)
    {
        // Validación base
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6',
            'new_password_confirmation' => 'required|same:new_password',
        ]);

        // Intentar obtener el usuario autenticado (sesión)
        $user = Auth::user();

        // Si no hay usuario autenticado (API stateless), requerimos email y buscamos el usuario
        if (! $user) {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);
            $user = User::where('email', $request->email)->first();
        }

        // Seguridad: si aún no hay usuario, devolvemos error genérico
        if (! $user) {
            return response()->json([
                'message' => 'No se pudo identificar al usuario',
            ], 401);
        }

        // Verificar la contraseña actual
        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'errors' => [
                    'current_password' => ['La contraseña actual es incorrecta'],
                ],
            ], 422);
        }

        // Actualizar la contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente',
        ]);
    }
}
