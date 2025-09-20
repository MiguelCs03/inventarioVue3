<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($validated)) {
            $user = Auth::user();

            // Crear un token simple (puedes usar Sanctum para algo más robusto)
            $token = bin2hex(random_bytes(32));

            // Obtener las secciones permitidas del usuario a través de sus roles
            $userPermissions = [];
            $allowedSections = [];

            // Obtener todos los permisos del usuario a través de sus roles
            foreach ($user->roles as $role) {
                foreach ($role->permissions as $permission) {
                    $userPermissions[] = $permission->name;
                    // Extraer la sección del nombre del permiso (ej: "compras" de "compras")
                    $allowedSections[] = $permission->name;
                }
            }

            // Si es admin, darle acceso a todas las secciones
            if ($user->hasRole('admin')) {
                $allowedSections = ['compras', 'productos', 'clientes', 'usuarios', 'proveedores', 'ordenes', 'reportes'];
            }

            // Generar reglas de habilidad para el frontend
            $userAbilityRules = [];
            foreach (array_unique($allowedSections) as $section) {
                $userAbilityRules[] = [
                    'action' => 'read',
                    'subject' => $section,
                ];
            }

            // Si es admin, darle acceso total
            if ($user->hasRole('admin')) {
                $userAbilityRules = [['action' => 'manage', 'subject' => 'all']];
            }

            return response()->json([
                'accessToken' => $token,
                'userData' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => $user->roles->pluck('nombre')->toArray(),
                    'permissions' => array_unique($userPermissions),
                    'allowedSections' => array_unique($allowedSections), // Secciones permitidas
                ],
                'userAbilityRules' => $userAbilityRules,
                // ruta inicial sugerida según rol (frontend puede usarla para redirigir)
                'homeRoute' => $this->homeRouteForRole($user->hasRole('admin') ? 'admin' : 'user'),
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
}
