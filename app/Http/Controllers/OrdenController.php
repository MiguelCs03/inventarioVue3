<?php

namespace App\Http\Controllers;

use App\Models\Orden;
use App\Models\OrdenItem;
use App\Models\OrdenPago;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrdenController extends Controller
{
    public function index(Request $request)
    {
        $query = Orden::with(['cliente', 'items.producto']);

        // Búsqueda por cliente
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('cliente', function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('apellido', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('estado') && $request->estado) {
            $query->where('estado', $request->estado);
        }

        // Ordenamiento
        $sortBy = $request->get('sortBy', 'fecha');
        $sortOrder = $request->get('sortOrder', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 10);
        $ordenes = $query->paginate($perPage);

        return response()->json($ordenes);
    }

    public function store(Request $request)
    {
        Log::info('OrdenController@store - Iniciando creación de orden', [
            'request_data' => $request->all(),
        ]);

        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date',
            'estado' => 'required|in:pendiente,confirmada,cancelada',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        Log::info('OrdenController@store - Validación pasada correctamente');

        DB::beginTransaction();

        try {
            Log::info('OrdenController@store - Creando orden', [
                'cliente_id' => $request->cliente_id,
                'fecha' => $request->fecha,
                'estado' => $request->estado,
            ]);

            // Crear la orden
            $orden = Orden::create([
                'cliente_id' => $request->cliente_id,
                'fecha' => $request->fecha,
                'estado' => $request->estado,
                'observaciones' => $request->observaciones,
                'total' => 0,
            ]);

            Log::info('OrdenController@store - Orden creada', ['orden_id' => $orden->id]);

            // Crear los items
            foreach ($request->items as $itemData) {
                $subtotal = $itemData['cantidad'] * $itemData['precio_unitario'];

                OrdenItem::create([
                    'orden_id' => $orden->id,
                    'producto_id' => $itemData['producto_id'],
                    'cantidad' => $itemData['cantidad'],
                    'precio_unitario' => $itemData['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
            }

            // Calcular el total
            $orden->calcularTotal();

            Log::info('OrdenController@store - Orden completada exitosamente', [
                'orden_id' => $orden->id,
                'total' => $orden->total,
            ]);

            DB::commit();

            return response()->json($orden->load(['cliente', 'items.producto']), 201);
        } catch (\Exception $e) {
            DB::rollback();

            Log::error('OrdenController@store - Error al crear orden', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
            ]);

            return response()->json(['error' => 'Error al crear la orden'], 500);
        }
    }

    public function show($id)
    {
        try {
            Log::info('OrdenController@show - Buscando orden', ['orden_id' => $id]);

            $orden = Orden::with(['cliente', 'items.producto'])->find($id);

            if (! $orden) {
                Log::warning('OrdenController@show - Orden no encontrada', ['orden_id' => $id]);

                return response()->json(['error' => 'Orden no encontrada'], 404);
            }

            Log::info('OrdenController@show - Orden encontrada', [
                'orden_id' => $orden->id,
                'cliente_id' => $orden->cliente_id,
                'items_count' => $orden->items->count(),
            ]);

            return response()->json($orden);
        } catch (\Exception $e) {
            Log::error('OrdenController@show - Error al obtener orden', [
                'orden_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Error al obtener la orden'], 500);
        }
    }

    public function update(Request $request, Orden $orden)
    {
        $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'fecha' => 'required|date',
            'estado' => 'required|in:pendiente,confirmada,cancelada',
            'observaciones' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            // Actualizar la orden
            $orden->update([
                'cliente_id' => $request->cliente_id,
                'fecha' => $request->fecha,
                'estado' => $request->estado,
                'observaciones' => $request->observaciones,
            ]);

            // Eliminar items existentes y crear nuevos
            $orden->items()->delete();

            foreach ($request->items as $itemData) {
                $subtotal = $itemData['cantidad'] * $itemData['precio_unitario'];

                OrdenItem::create([
                    'orden_id' => $orden->id,
                    'producto_id' => $itemData['producto_id'],
                    'cantidad' => $itemData['cantidad'],
                    'precio_unitario' => $itemData['precio_unitario'],
                    'subtotal' => $subtotal,
                ]);
            }

            // Calcular el total
            $orden->calcularTotal();

            DB::commit();

            return response()->json($orden->load(['cliente', 'items.producto']));
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => 'Error al actualizar la orden'], 500);
        }
    }

    public function confirmar(Request $request, Orden $orden)
    {
        $request->validate([
            'pagos' => 'required|array|min:1',
            'pagos.*.metodo_pago_id' => 'required|exists:metodos_pago,id',
            'pagos.*.monto' => 'required|numeric|min:0.01',
            'pagos.*.detalle' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            // Verificar que la orden esté pendiente
            if ($orden->estado !== 'pendiente') {
                return response()->json(['error' => 'Solo se pueden confirmar órdenes pendientes'], 400);
            }

            // Calcular total de pagos
            $totalPagos = collect($request->pagos)->sum('monto');

            // Verificar que el total de pagos coincida con el total de la orden
            if (abs($totalPagos - $orden->total) > 0.01) {
                return response()->json(['error' => 'El total de pagos no coincide con el total de la orden'], 400);
            }

            // Verificar disponibilidad de stock para todos los productos
            foreach ($orden->items as $item) {
                $producto = $item->producto;
                if ($producto->stock < $item->cantidad) {
                    return response()->json([
                        'error' => "Stock insuficiente para el producto: {$producto->nombre}. Disponible: {$producto->stock}, Requerido: {$item->cantidad}",
                    ], 400);
                }
            }

            // Descontar stock de los productos
            foreach ($orden->items as $item) {
                $producto = $item->producto;
                $producto->stock -= $item->cantidad;
                $producto->save();
            }

            // Registrar los pagos
            foreach ($request->pagos as $pagoData) {
                OrdenPago::create([
                    'orden_id' => $orden->id,
                    'metodo_pago_id' => $pagoData['metodo_pago_id'],
                    'monto' => $pagoData['monto'],
                    'detalle' => $pagoData['detalle'] ?? null,
                ]);
            }

            // Actualizar la orden
            $orden->update([
                'estado' => 'confirmada',
                'fecha_confirmacion' => now(),
                'total_pagado' => $totalPagos,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Orden confirmada exitosamente',
                'orden' => $orden->load(['cliente', 'items.producto', 'pagos.metodoPago']),
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json(['error' => 'Error al confirmar la orden: '.$e->getMessage()], 500);
        }
    }

    public function destroy(Orden $orden)
    {
        try {
            $orden->delete();

            return response()->json(['message' => 'Orden eliminada exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al eliminar la orden'], 500);
        }
    }
}
