<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'TodoCamisetas API',
    description: 'API REST para gestionar camisetas, clientes, tallas y calcular precio final segun el cliente consultado.'
)]
#[OA\Server(
    url: 'http://localhost:8080/api',
    description: 'Servidor de desarrollo local'
)]
#[OA\Tag(name: 'Health', description: 'Estado del servicio')]
#[OA\Tag(name: 'Camisetas', description: 'Gestion de camisetas')]
#[OA\Tag(name: 'Clientes', description: 'Gestion de clientes')]
#[OA\Tag(name: 'Tallas', description: 'Gestion de tallas y asociaciones')]
abstract class Controller
{
    use ApiResponse;

    protected function validationMessages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'string' => 'El campo :attribute debe ser texto.',
            'max' => 'El campo :attribute no puede superar :max caracteres.',
            'email' => 'El campo :attribute debe ser un correo electronico valido.',
            'unique' => 'El valor ingresado para :attribute ya existe.',
            'in' => 'El campo :attribute contiene un valor no permitido.',
            'numeric' => 'El campo :attribute debe ser numerico.',
            'integer' => 'El campo :attribute debe ser un numero entero.',
            'min' => 'El campo :attribute debe ser al menos :min.',
            'max.numeric' => 'El campo :attribute no puede ser mayor a :max.',
            'lte' => 'El campo :attribute no puede ser mayor que :value.',
            'exists' => 'El valor seleccionado para :attribute no existe.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'titulo' => 'titulo',
            'club' => 'club',
            'pais' => 'pais',
            'tipo' => 'tipo',
            'color' => 'color',
            'precio' => 'precio',
            'precio_oferta' => 'precio de oferta',
            'detalles' => 'detalles',
            'codigo_producto' => 'codigo de producto',
            'nombre_comercial' => 'nombre comercial',
            'rut' => 'rut',
            'direccion' => 'direccion',
            'categoria' => 'categoria',
            'contacto_nombre' => 'nombre de contacto',
            'contacto_email' => 'correo de contacto',
            'porcentaje_oferta' => 'porcentaje de oferta',
            'nombre' => 'nombre',
            'talla_id' => 'talla',
        ];
    }
}
