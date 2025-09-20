<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CompraController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = \App\Models\Compra::with(['proveedor', 'items.producto']);

        // Filtros
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('proveedor', function ($q) use ($search) {
                $q->where('nombre', 'like', "%$search%");
            });
        }
        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        $perPage = $request->get('per_page', 10);
        $compras = $query->orderByDesc('fecha')->paginate($perPage);

        return response()->json($compras);
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
            'proveedor_id' => 'required|exists:proveedors,id',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        \DB::beginTransaction();
        try {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += $item['cantidad'] * $item['precio_unitario'];
            }
            $compra = \App\Models\Compra::create([
                'proveedor_id' => $data['proveedor_id'],
                'fecha' => $data['fecha'],
                'total' => $total,
                'estado' => 'registrada',
                'observaciones' => $data['observaciones'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $compraItem = \App\Models\CompraItem::create([
                    'compra_id' => $compra->id,
                    'producto_id' => $item['producto_id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $item['cantidad'] * $item['precio_unitario'],
                ]);
                // Actualizar stock del producto
                $producto = \App\Models\Producto::find($item['producto_id']);
                $producto->stock += $item['cantidad'];
                $producto->save();
            }

            \DB::commit();

            return response()->json($compra->load(['proveedor', 'items.producto']), 201);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['error' => 'Error al registrar la compra: '.$e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
