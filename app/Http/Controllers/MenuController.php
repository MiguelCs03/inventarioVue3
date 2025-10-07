<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    // Devuelve todos los menús con sus submenús activos y ordenados
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Log para depuración
        $permissions = [];
        $allowedSections = [];
        $isAdmin = false;
        
        if ($user) {
            // Cargar roles y permisos del usuario actual
            $user->loadMissing('roles.permissions');
            
            foreach ($user->roles as $role) {
                if (strtolower($role->nombre) === 'admin') {
                    $isAdmin = true;
                    break;
                }
                
                foreach ($role->permissions as $permission) {
                    $permissions[] = $permission->name;
                    // Usar subject como sección permitida; fallback al name si no hay subject
                    $section = $permission->subject ?: $permission->name;
                    if (!empty($section)) {
                        $allowedSections[] = trim(strtolower($section));
                    }
                }
            }
            
            // Asegurar que dashboard-inicio siempre sea accesible
            $allowedSections[] = 'dashboard-inicio';
            $allowedSections = array_unique(array_map('strtolower', $allowedSections));
        }
        
        // Log para depuración
        Log::debug('Cargando menús para usuario', [
            'user_id' => $user?->id,
            'is_admin' => $isAdmin,
            'permissions' => $permissions,
            'allowed_sections' => $allowedSections,
        ]);
        
        // Obtener menús
        $menus = Menu::with(['submenus' => function($q) {
            $q->where('is_active', true)->orderBy('order');
        }])
        ->where('is_active', true)
        ->orderBy('order')
        ->get();
        
        // Normalizar los campos de section
        $menus->each(function ($menu) {
            // Si no tiene sección definida, usar la ruta o el nombre como sección
            if (empty($menu->section)) {
                $routeKey = str_replace('/', '', $menu->route ?? '');
                $menu->section = $routeKey ?: strtolower($menu->name);
            }
            
            // Hacer lo mismo para los submenús
            $menu->submenus->each(function ($submenu) {
                if (empty($submenu->section)) {
                    $routeKey = str_replace('/', '', $submenu->route ?? '');
                    $submenu->section = $routeKey ?: strtolower($submenu->name);
                }
            });
        });
        
        return response()->json($menus);
    }
}
