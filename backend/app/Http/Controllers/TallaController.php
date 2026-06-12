<?php

namespace App\Http\Controllers;

use App\Models\Camiseta;
use App\Models\Talla;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TallaController extends Controller
{
    public function index(): JsonResponse
    {
        return $this->successResponse(Talla::query()->orderBy('nombre')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:20|unique:tallas,nombre',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        return $this->successResponse(Talla::create($validator->validated()), 201);
    }

    public function show(string $id): JsonResponse
    {
        $talla = Talla::find($id);

        if (! $talla) {
            return $this->errorResponse('Talla no encontrada.', 404);
        }

        return $this->successResponse($talla);
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $talla = Talla::find($id);

        if (! $talla) {
            return $this->errorResponse('Talla no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|string|max:20|unique:tallas,nombre,'.$talla->id,
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        $talla->update($validator->validated());

        return $this->successResponse($talla->fresh());
    }

    public function destroy(string $id): JsonResponse
    {
        $talla = Talla::find($id);

        if (! $talla) {
            return $this->errorResponse('Talla no encontrada.', 404);
        }

        if ($talla->camisetas()->exists()) {
            return $this->errorResponse('No se puede eliminar una talla asociada a camisetas.', 409);
        }

        $talla->delete();

        return $this->successResponse(['message' => 'Talla eliminada exitosamente.']);
    }

    public function listByCamiseta(string $id): JsonResponse
    {
        $camiseta = Camiseta::with('tallas')->find($id);

        if (! $camiseta) {
            return $this->errorResponse('Camiseta no encontrada.', 404);
        }

        return $this->successResponse($camiseta->tallas);
    }

    public function attachToCamiseta(Request $request, string $id): JsonResponse
    {
        $camiseta = Camiseta::with('tallas')->find($id);

        if (! $camiseta) {
            return $this->errorResponse('Camiseta no encontrada.', 404);
        }

        $validator = Validator::make($request->all(), [
            'talla_id' => 'required|integer|exists:tallas,id',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse('Los datos enviados no son validos.', 422, $validator->errors()->toArray());
        }

        $tallaId = (int) $validator->validated()['talla_id'];

        if ($camiseta->tallas()->where('tallas.id', $tallaId)->exists()) {
            return $this->errorResponse('La talla ya esta asociada a la camiseta.', 409);
        }

        $camiseta->tallas()->attach($tallaId);

        return $this->successResponse($camiseta->fresh('tallas'));
    }

    public function detachFromCamiseta(string $id, string $tallaId): JsonResponse
    {
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
    }
}
