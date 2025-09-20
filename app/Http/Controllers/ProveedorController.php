<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Proveedor::query();
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%$search%")
                  ->orWhere('contacto', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }
        if ($request->has('activo')) {
            $query->where('activo', $request->activo);
        }
        $perPage = $request->get('per_page', 10);
        $proveedores = $query->orderBy('nombre')->paginate($perPage);
        return response()->json($proveedores);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'contacto' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ]);
        $proveedor = \App\Models\Proveedor::create($data);
        return response()->json($proveedor, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $proveedor = \App\Models\Proveedor::findOrFail($id);
        return response()->json($proveedor);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $proveedor = \App\Models\Proveedor::findOrFail($id);
        $data = $request->validate([
            'nombre' => 'required|string|max:100',
            'contacto' => 'nullable|string|max:100',
            'email' => 'nullable|email',
            'telefono' => 'nullable|string|max:20',
            'activo' => 'boolean',
        ]);
        $proveedor->update($data);
        return response()->json($proveedor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $proveedor = \App\Models\Proveedor::findOrFail($id);
        $proveedor->delete();
        return response()->json(['message' => 'Proveedor eliminado']);
    }

    // Activar proveedor
    public function activate($id)
    {
        $proveedor = \App\Models\Proveedor::findOrFail($id);
        $proveedor->activo = true;
        $proveedor->save();
        return response()->json($proveedor);
    }

    // Desactivar proveedor
    public function deactivate($id)
    {
        $proveedor = \App\Models\Proveedor::findOrFail($id);
        $proveedor->activo = false;
        $proveedor->save();
        return response()->json($proveedor);
    }
}
