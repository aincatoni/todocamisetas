<?php

namespace App\Http\Controllers;

use App\Models\Camiseta;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CamisetaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Camiseta::query();

        foreach (['club', 'pais', 'tipo', 'color'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->string($filter)->toString());
            }
        }

        return $this->successResponse($query->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'club' => 'required|string|max:255',
            'pais' => 'required|string|max:255',
            'tipo' => 'required|string|max:100',
            'color' => 'required|string|max:100',
            'precio' => 'required|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0|lte:precio',
            'detalles' => 'nullable|string',
            'codigo_producto' => 'required|string|max:100|unique:camisetas,codigo_producto',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Camiseta::create($validator->validated()), 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $camiseta = Camiseta::with('tallas')->find($id);

        if (! $camiseta) {
            return $this->errorResponse('Camiseta no encontrada.', 404);
        }

        $payload = $camiseta->toArray();
        $payload['precio_final'] = $camiseta->precio;

        if ($request->filled('cliente_id')) {
            $cliente = Cliente::find($request->input('cliente_id'));

            if (! $cliente) {
                return $this->errorResponse('Cliente no encontrado.', 404);
            }

            $payload['cliente_consultado'] = [
                'id' => $cliente->id,
                'categoria' => $cliente->categoria,
            ];
            $payload['precio_final'] = $this->resolverPrecioFinal($camiseta, $cliente);
        }

        return $this->successResponse($payload);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $camiseta = Camiseta::find($id);

        if (! $camiseta) {
            return $this->errorResponse('Camiseta no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'titulo' => 'sometimes|string|max:255',
            'club' => 'sometimes|string|max:255',
            'pais' => 'sometimes|string|max:255',
            'tipo' => 'sometimes|string|max:100',
            'color' => 'sometimes|string|max:100',
            'precio' => 'sometimes|numeric|min:0',
            'precio_oferta' => 'nullable|numeric|min:0',
            'detalles' => 'nullable|string',
            'codigo_producto' => 'sometimes|string|max:100|unique:camisetas,codigo_producto,'.$camiseta->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        $data = $validator->validated();

        if (array_key_exists('precio_oferta', $data)) {
            $precioBase = $data['precio'] ?? $camiseta->precio;

            if (! is_null($data['precio_oferta']) && $data['precio_oferta'] > $precioBase) {
                return $this->errorResponse('El precio de oferta no puede ser mayor al precio base.', 422, [
                    'precio_oferta' => ['El precio de oferta no puede ser mayor al precio base.'],
                ]);
            }
        }

        $camiseta->update($data);

        return $this->successResponse($camiseta->fresh('tallas'));
    }

    public function destroy(string $id): JsonResponse
    {
        $camiseta = Camiseta::find($id);

        if (! $camiseta) {
            return $this->errorResponse('Camiseta no encontrada.', 404);
        }

        $camiseta->delete();

        return $this->successResponse(['message' => 'Camiseta eliminada exitosamente.']);
    }

    private function resolverPrecioFinal(Camiseta $camiseta, Cliente $cliente): float|int|string
    {
        if ($cliente->categoria === 'Preferencial' && ! is_null($camiseta->precio_oferta)) {
            return $camiseta->precio_oferta;
        }

        return $camiseta->precio;
    }
}
