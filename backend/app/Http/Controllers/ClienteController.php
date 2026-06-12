<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class ClienteController extends Controller
{
    #[OA\Get(
        path: '/clientes',
        operationId: 'getClientes',
        tags: ['Clientes'],
        summary: 'Listar clientes',
        parameters: [new OA\Parameter(name: 'categoria', in: 'query', required: false, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Listado exitoso')]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Cliente::query();

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->string('categoria')->toString());
        }

        return $this->successResponse($query->get());
    }

    #[OA\Post(
        path: '/clientes',
        operationId: 'createCliente',
        tags: ['Clientes'],
        summary: 'Crear cliente',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nombre_comercial', 'rut', 'direccion', 'categoria', 'contacto_nombre', 'contacto_email'],
                properties: [
                    new OA\Property(property: 'nombre_comercial', type: 'string', example: '90minutos'),
                    new OA\Property(property: 'rut', type: 'string', example: '76123456-7'),
                    new OA\Property(property: 'direccion', type: 'string', example: 'Av. Apoquindo 1234, Las Condes'),
                    new OA\Property(property: 'categoria', type: 'string', example: 'Preferencial'),
                    new OA\Property(property: 'contacto_nombre', type: 'string', example: 'Carla Paredes'),
                    new OA\Property(property: 'contacto_email', type: 'string', format: 'email', example: 'compras@90minutos.cl'),
                    new OA\Property(property: 'porcentaje_oferta', type: 'number', format: 'float', nullable: true, example: 10),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Cliente creado'),
            new OA\Response(response: 422, description: 'Errores de validacion'),
        ]
    )]
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
        ], $this->validationMessages(), $this->validationAttributes());

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Cliente::create($validator->validated()), 201);
    }

    #[OA\Get(
        path: '/clientes/{id}',
        operationId: 'getCliente',
        tags: ['Clientes'],
        summary: 'Obtener cliente por ID',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        responses: [
            new OA\Response(response: 200, description: 'Cliente encontrado'),
            new OA\Response(response: 404, description: 'Cliente no encontrado'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        $cliente = Cliente::find($id);

        if (! $cliente) {
            return $this->errorResponse('Cliente no encontrado.', 404);
        }

        return $this->successResponse($cliente);
    }

    #[OA\Put(
        path: '/clientes/{id}',
        operationId: 'updateCliente',
        tags: ['Clientes'],
        summary: 'Actualizar cliente',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Cliente actualizado'),
            new OA\Response(response: 404, description: 'Cliente no encontrado'),
            new OA\Response(response: 422, description: 'Errores de validacion'),
        ]
    )]
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
        ], $this->validationMessages(), $this->validationAttributes());

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        $cliente->update($validator->validated());

        return $this->successResponse($cliente->fresh());
    }

    #[OA\Delete(
        path: '/clientes/{id}',
        operationId: 'deleteCliente',
        tags: ['Clientes'],
        summary: 'Eliminar cliente',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        responses: [
            new OA\Response(response: 200, description: 'Cliente eliminado'),
            new OA\Response(response: 404, description: 'Cliente no encontrado'),
        ]
    )]
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
