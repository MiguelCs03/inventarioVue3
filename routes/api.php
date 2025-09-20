<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\OrdenController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

// Ruta de prueba para logs
Route::get('/test-log', function () {
    Log::info('Test log funcionando correctamente', ['timestamp' => now()]);

    return response()->json(['message' => 'Log test ejecutado']);
});

Route::get('/users', [UserController::class, 'index']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::put('/users/{id}/deactivate', [UserController::class, 'deactivate']);
Route::put('/users/{id}/activate', [UserController::class, 'activate']);

Route::post('/login', [AuthController::class, 'login'])->name('api.login');
Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

// Rutas para roles
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::put('/roles/{id}/deactivate', [RoleController::class, 'deactivate']);
Route::put('/roles/{id}/activate', [RoleController::class, 'activate']);

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
