<?php

namespace Database\Seeders;

use App\Models\Cliente;
use Illuminate\Database\Seeder;

class ClienteSeeder extends Seeder
{
    public function run(): void
    {
        Cliente::updateOrCreate(
            ['rut' => '76123456-7'],
            [
                'nombre_comercial' => '90minutos',
                'direccion' => 'Av. Apoquindo 1234, Las Condes',
                'categoria' => 'Preferencial',
                'contacto_nombre' => 'Carla Paredes',
                'contacto_email' => 'compras@90minutos.cl',
                'porcentaje_oferta' => 10,
            ]
        );

        Cliente::updateOrCreate(
            ['rut' => '76987654-3'],
            [
                'nombre_comercial' => 'tdeportes',
                'direccion' => 'Av. Libertador 456, Santiago',
                'categoria' => 'Regular',
                'contacto_nombre' => 'Diego Soto',
                'contacto_email' => 'ventas@tdeportes.cl',
                'porcentaje_oferta' => 0,
            ]
        );
    }
}
