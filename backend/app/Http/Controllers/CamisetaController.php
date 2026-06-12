<?php

namespace App\Http\Controllers;

use App\Models\Camiseta;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class CamisetaController extends Controller
{
    #[OA\Get(
        path: '/camisetas',
        operationId: 'getCamisetas',
        tags: ['Camisetas'],
        summary: 'Listar camisetas',
        parameters: [
            new OA\Parameter(name: 'club', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'pais', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'tipo', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'color', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Listado exitoso')]
    )]
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

    #[OA\Post(
        path: '/camisetas',
        operationId: 'createCamiseta',
        tags: ['Camisetas'],
        summary: 'Crear camiseta',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titulo', 'club', 'pais', 'tipo', 'color', 'precio', 'codigo_producto'],
                properties: [
                    new OA\Property(property: 'titulo', type: 'string', example: 'Camiseta Local 2025'),
                    new OA\Property(property: 'club', type: 'string', example: 'Seleccion Chilena'),
                    new OA\Property(property: 'pais', type: 'string', example: 'Chile'),
                    new OA\Property(property: 'tipo', type: 'string', example: 'Local'),
                    new OA\Property(property: 'color', type: 'string', example: 'Rojo'),
                    new OA\Property(property: 'precio', type: 'number', format: 'float', example: 45000),
                    new OA\Property(property: 'precio_oferta', type: 'number', format: 'float', nullable: true, example: 39990),
                    new OA\Property(property: 'detalles', type: 'string', nullable: true, example: 'Tela dry-fit, version hincha.'),
                    new OA\Property(property: 'codigo_producto', type: 'string', example: 'CHI-LOC-2025-01'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Camiseta creada'),
            new OA\Response(response: 422, description: 'Errores de validacion'),
        ]
    )]
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
        ], $this->validationMessages(), $this->validationAttributes());

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Camiseta::create($validator->validated()), 201);
    }

    #[OA\Get(
        path: '/camisetas/{id}',
        operationId: 'getCamiseta',
        tags: ['Camisetas'],
        summary: 'Obtener camiseta por ID',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'cliente_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'), example: 1),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Camiseta encontrada'),
            new OA\Response(response: 404, description: 'Camiseta o cliente no encontrado'),
        ]
    )]
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

    #[OA\Put(
        path: '/camisetas/{id}',
        operationId: 'updateCamiseta',
        tags: ['Camisetas'],
        summary: 'Actualizar camiseta',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Camiseta actualizada'),
            new OA\Response(response: 404, description: 'Camiseta no encontrada'),
            new OA\Response(response: 422, description: 'Errores de validacion'),
        ]
    )]
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
        ], $this->validationMessages(), $this->validationAttributes());

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

    #[OA\Delete(
        path: '/camisetas/{id}',
        operationId: 'deleteCamiseta',
        tags: ['Camisetas'],
        summary: 'Eliminar camiseta',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        responses: [
            new OA\Response(response: 200, description: 'Camiseta eliminada'),
            new OA\Response(response: 404, description: 'Camiseta no encontrada'),
        ]
    )]
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
