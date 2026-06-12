<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClienteController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->string('categoria')->toString());
        }

        return $this->successResponse($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre_comercial' => 'required|string|max:255',
            'rut' => 'required|string|max:20|unique:clientes,rut',
            'direccion' => 'required|string|max:255',
            'categoria' => 'required|in:Regular,Preferencial',
            'contacto_nombre' => 'required|string|max:255',
            'contacto_email' => 'required|email|max:255',
            'porcentaje_oferta' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Cliente::create($validator->validated()), 201);
    }

    public function show(string $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (! $cliente) {
            return $this->errorResponse('Cliente no encontrado.', 404);
        }

        return $this->successResponse($cliente);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (! $cliente) {
            return $this->errorResponse('Cliente no encontrado.', 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre_comercial' => 'sometimes|string|max:255',
            'rut' => 'sometimes|string|max:20|unique:clientes,rut,'.$cliente->id,
            'direccion' => 'sometimes|string|max:255',
            'categoria' => 'sometimes|in:Regular,Preferencial',
            'contacto_nombre' => 'sometimes|string|max:255',
            'contacto_email' => 'sometimes|email|max:255',
            'porcentaje_oferta' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        $cliente->update($validator->validated());

        return $this->successResponse($cliente->fresh());
    }

    public function destroy(string $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (! $cliente) {
            return $this->errorResponse('Cliente no encontrado.', 404);
        }

        $cliente->delete();

        return $this->successResponse(['message' => 'Cliente eliminado exitosamente.']);
    }
}
