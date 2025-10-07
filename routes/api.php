<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Ruta de prueba para logs
Route::get('/test-log', function () {
    Log::info('Test log funcionando correctamente', ['timestamp' => now()]);

    return response()->json(['message' => 'Log test ejecutado']);
});

// Ruta de depuración para verificar roles/permisos y secciones permitidas del usuario autenticado
Route::get('/debug/auth', function () {
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'authenticated' => false,
            'message' => 'No hay usuario autenticado en la sesión actual',
            'db_connection' => config('database.default'),
        ]);
    }

    $user->loadMissing('roles.permissions');

    $adminRoles = ['admin', 'administrador', 'administrator'];
    $isAdmin = false;
    $allowedSections = [];
    $permissionsList = [];
    $roles = [];

    foreach ($user->roles as $role) {
        $roles[] = $role->nombre;
        if (in_array(strtolower($role->nombre), $adminRoles)) {
            $isAdmin = true;
        }

        foreach ($role->permissions as $permission) {
            $permissionsList[] = [
                'id' => $permission->id,
                'name' => $permission->name,
                'action' => $permission->action,
                'subject' => $permission->subject,
            ];

            $section = $permission->subject ?: $permission->name;
            if ($section) {
                $allowedSections[] = strtolower(trim($section));
            }
        }
    }

    // dashboard-inicio siempre accesible para autenticados
    $allowedSections[] = 'dashboard-inicio';
    $allowedSections = array_values(array_unique($allowedSections));

    return response()->json([
        'authenticated' => true,
        'db_connection' => config('database.default'),
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'roles' => $roles,
            'isAdmin' => $isAdmin,
        ],
        'permissions' => $permissionsList,
        'allowedSections' => $allowedSections,
    ]);
});

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::put('/users/{id}/deactivate', [UserController::class, 'deactivate']);
Route::put('/users/{id}/activate', [UserController::class, 'activate']);

// Rutas para perfil de usuario
Route::get('/profile', [UserController::class, 'profile']);
Route::put('/profile', [UserController::class, 'updateProfile']);

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');
// Cambio de contraseña (stateless o por sesión)
Route::put('/profile/password', [AuthController::class, 'updatePassword'])->name('api.profile.password');

// Rutas para roles
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::put('/roles/{id}/deactivate', [RoleController::class, 'deactivate']);
Route::put('/roles/{id}/activate', [RoleController::class, 'activate']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);

// Rutas para permisos
Route::get('/permissions', [PermissionController::class, 'index']);
Route::get('/roles/{roleId}/permissions', [PermissionController::class, 'byRole']);
Route::put('/roles/{roleId}/permissions', [PermissionController::class, 'syncForRole']);

// Rutas para menús
Route::get('/menus', [MenuController::class, 'index']);

// Rutas para clientes
Route::apiResource('clientes', ClienteController::class);
Route::put('/clientes/{cliente}/activate', [ClienteController::class, 'activate']);
Route::put('/clientes/{cliente}/deactivate', [ClienteController::class, 'deactivate']);

// Rutas para productos
Route::apiResource('productos', ProductoController::class);

// Rutas para órdenes
Route::apiResource('ordenes', OrdenController::class);
Route::post('/ordenes/{orden}/confirmar', [OrdenController::class, 'confirmar']);

// Rutas para métodos de pago
Route::apiResource('metodos-pago', MetodoPagoController::class);

// Rutas para proveedores
Route::apiResource('proveedores', App\Http\Controllers\ProveedorController::class);
Route::put('/proveedores/{proveedor}/activate', [App\Http\Controllers\ProveedorController::class, 'activate']);
Route::put('/proveedores/{proveedor}/deactivate', [App\Http\Controllers\ProveedorController::class, 'deactivate']);

// Rutas para compras
Route::apiResource('compras', App\Http\Controllers\CompraController::class);
