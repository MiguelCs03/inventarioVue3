<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    // Devuelve todos los menús con sus submenús activos y ordenados
    public function index(Request $request)
    {
        $menus = Menu::with(['submenus' => function($q) {
            $q->where('is_active', true)->orderBy('order');
        }])
        ->where('is_active', true)
        ->orderBy('order')
        ->get();

        return response()->json($menus);
    }
}
