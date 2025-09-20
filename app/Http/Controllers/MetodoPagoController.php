<?php

namespace App\Http\Controllers;

use App\Models\MetodoPago;
use Illuminate\Http\Request;

class MetodoPagoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $metodosPago = MetodoPago::where('activo', true)
            ->orderBy('nombre')
            ->get();

        return response()->json($metodosPago);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:metodos_pago',
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $metodoPago = MetodoPago::create($request->all());

        return response()->json($metodoPago, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(MetodoPago $metodoPago)
    {
        return response()->json($metodoPago);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MetodoPago $metodoPago)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'codigo' => 'required|string|max:50|unique:metodos_pago,codigo,' . $metodoPago->id,
            'descripcion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $metodoPago->update($request->all());

        return response()->json($metodoPago);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MetodoPago $metodoPago)
    {
        $metodoPago->delete();

        return response()->json(['message' => 'Método de pago eliminado exitosamente']);
    }
}
