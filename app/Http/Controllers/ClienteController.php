<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        $query = Cliente::query();

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                  ->orWhere('apellido', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por estado
        if ($request->has('activo') && $request->activo !== null) {
            $query->where('activo', (bool) $request->activo);
        }

        // Ordenamiento
        $sortBy = $request->get('sortBy', 'nombre');
        $sortOrder = $request->get('sortOrder', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Paginación
        $perPage = $request->get('per_page', 10);
        $clientes = $query->paginate($perPage);

        return response()->json($clientes);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|unique:clientes,email',
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $cliente = Cliente::create($request->all());

        return response()->json($cliente, 201);
    }

    public function show(Cliente $cliente)
    {
        return response()->json($cliente->load('ordenes'));
    }

    public function update(Request $request, Cliente $cliente)
    {
        $request->validate([
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => [
                'required',
                'email',
                Rule::unique('clientes')->ignore($cliente->id)
            ],
            'telefono' => 'nullable|string|max:20',
            'direccion' => 'nullable|string',
            'activo' => 'boolean'
        ]);

        $cliente->update($request->all());

        return response()->json($cliente);
    }

    public function destroy(Cliente $cliente)
    {
        try {
            $cliente->delete();
            return response()->json(['message' => 'Cliente eliminado exitosamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se puede eliminar el cliente'], 409);
        }
    }

    public function activate(Cliente $cliente)
    {
        $cliente->update(['activo' => true]);
        return response()->json(['message' => 'Cliente activado exitosamente']);
    }

    public function deactivate(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);
        return response()->json(['message' => 'Cliente desactivado exitosamente']);
    }
}
