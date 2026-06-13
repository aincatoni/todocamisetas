<?php

namespace App\Http\Controllers;

use App\Models\Camiseta;
use App\Models\Talla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Throwable;

class TallaController extends Controller
{
    #[OA\Get(
        path: '/tallas',
        operationId: 'getTallas',
        tags: ['Tallas'],
        summary: 'Listar tallas',
        responses: [new OA\Response(response: 200, description: 'Listado exitoso')]
    )]
    public function index(): JsonResponse
    {
        try {
            return $this->successResponse(Talla::query()->orderBy('nombre')->get());
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible listar las tallas.');
        }
    }

    #[OA\Post(
        path: '/tallas',
        operationId: 'createTalla',
        tags: ['Tallas'],
        summary: 'Crear talla',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['nombre'], properties: [new OA\Property(property: 'nombre', type: 'string', example: 'M')])
        ),
        responses: [
            new OA\Response(response: 201, description: 'Talla creada'),
            new OA\Response(response: 422, description: 'Errores de validacion'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:20|unique:tallas,nombre',
            ], $this->validationMessages(), $this->validationAttributes());

            if ($validator->fails()) {
                return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
            }

            return $this->successResponse(Talla::create($validator->validated()), 201);
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible crear la talla.');
        }
    }

    #[OA\Get(
        path: '/tallas/{id}',
        operationId: 'getTalla',
        tags: ['Tallas'],
        summary: 'Obtener talla por ID',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        responses: [
            new OA\Response(response: 200, description: 'Talla encontrada'),
            new OA\Response(response: 404, description: 'Talla no encontrada'),
        ]
    )]
    public function show(string $id): JsonResponse
    {
        try {
            $talla = Talla::find($id);

            if (! $talla) {
                return $this->errorResponse('Talla no encontrada.', 404);
            }

            return $this->successResponse($talla);
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible obtener la talla.');
        }
    }

    #[OA\Put(
        path: '/tallas/{id}',
        operationId: 'updateTalla',
        tags: ['Tallas'],
        summary: 'Actualizar talla',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(type: 'object')),
        responses: [
            new OA\Response(response: 200, description: 'Talla actualizada'),
            new OA\Response(response: 404, description: 'Talla no encontrada'),
            new OA\Response(response: 422, description: 'Errores de validacion'),
        ]
    )]
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $talla = Talla::find($id);

            if (! $talla) {
                return $this->errorResponse('Talla no encontrada.', 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'sometimes|string|max:20|unique:tallas,nombre,'.$talla->id,
            ], $this->validationMessages(), $this->validationAttributes());

            if ($validator->fails()) {
                return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
            }

            $talla->update($validator->validated());

            return $this->successResponse($talla->fresh());
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible actualizar la talla.');
        }
    }

    #[OA\Delete(
        path: '/tallas/{id}',
        operationId: 'deleteTalla',
        tags: ['Tallas'],
        summary: 'Eliminar talla',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        responses: [
            new OA\Response(response: 200, description: 'Talla eliminada'),
            new OA\Response(response: 404, description: 'Talla no encontrada'),
            new OA\Response(response: 409, description: 'Talla asociada a camisetas'),
        ]
    )]
    public function destroy(string $id): JsonResponse
    {
        try {
            $talla = Talla::find($id);

            if (! $talla) {
                return $this->errorResponse('Talla no encontrada.', 404);
            }

            if ($talla->camisetas()->exists()) {
                return $this->errorResponse('No se puede eliminar una talla asociada a camisetas.', 409);
            }

            $talla->delete();

            return $this->successResponse(['message' => 'Talla eliminada exitosamente.']);
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible eliminar la talla.');
        }
    }

    #[OA\Get(
        path: '/camisetas/{id}/tallas',
        operationId: 'getTallasByCamiseta',
        tags: ['Tallas'],
        summary: 'Listar tallas asociadas a una camiseta',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        responses: [
            new OA\Response(response: 200, description: 'Listado exitoso'),
            new OA\Response(response: 404, description: 'Camiseta no encontrada'),
        ]
    )]
    public function listByCamiseta(string $id): JsonResponse
    {
        try {
            $camiseta = Camiseta::with('tallas')->find($id);

            if (! $camiseta) {
                return $this->errorResponse('Camiseta no encontrada.', 404);
            }

            return $this->successResponse($camiseta->tallas);
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible obtener las tallas de la camiseta.');
        }
    }

    #[OA\Post(
        path: '/camisetas/{id}/tallas',
        operationId: 'attachTallaToCamiseta',
        tags: ['Tallas'],
        summary: 'Asociar talla a camiseta',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1)],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(required: ['talla_id'], properties: [new OA\Property(property: 'talla_id', type: 'integer', example: 2)])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Talla asociada'),
            new OA\Response(response: 404, description: 'Camiseta no encontrada'),
            new OA\Response(response: 409, description: 'Talla ya asociada'),
            new OA\Response(response: 422, description: 'Errores de validacion'),
        ]
    )]
    public function attachToCamiseta(Request $request, string $id): JsonResponse
    {
        try {
            $camiseta = Camiseta::with('tallas')->find($id);

            if (! $camiseta) {
                return $this->errorResponse('Camiseta no encontrada.', 404);
            }

            $validator = Validator::make($request->all(), [
                'talla_id' => 'required|integer|exists:tallas,id',
            ], $this->validationMessages(), $this->validationAttributes());

            if ($validator->fails()) {
                return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
            }

            $tallaId = (int) $validator->validated()['talla_id'];

            if ($camiseta->tallas()->where('tallas.id', $tallaId)->exists()) {
                return $this->errorResponse('La talla ya esta asociada a la camiseta.', 409);
            }

            $camiseta->tallas()->attach($tallaId);

            return $this->successResponse($camiseta->fresh('tallas'));
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible asociar la talla a la camiseta.');
        }
    }

    #[OA\Delete(
        path: '/camisetas/{id}/tallas/{tallaId}',
        operationId: 'detachTallaFromCamiseta',
        tags: ['Tallas'],
        summary: 'Desasociar talla de camiseta',
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 1),
            new OA\Parameter(name: 'tallaId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), example: 2),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Talla desasociada'),
            new OA\Response(response: 404, description: 'Camiseta, talla o asociacion no encontrada'),
        ]
    )]
    public function detachFromCamiseta(string $id, string $tallaId): JsonResponse
    {
        try {
            $camiseta = Camiseta::with('tallas')->find($id);

            if (! $camiseta) {
                return $this->errorResponse('Camiseta no encontrada.', 404);
            }

            $talla = Talla::find($tallaId);

            if (! $talla) {
                return $this->errorResponse('Talla no encontrada.', 404);
            }

            if (! $camiseta->tallas()->where('tallas.id', $talla->id)->exists()) {
                return $this->errorResponse('La talla no esta asociada a la camiseta.', 404);
            }

            $camiseta->tallas()->detach($talla->id);

            return $this->successResponse($camiseta->fresh('tallas'));
        } catch (Throwable $exception) {
            return $this->serverErrorResponse($exception, 'No fue posible desasociar la talla de la camiseta.');
        }
    }
}
