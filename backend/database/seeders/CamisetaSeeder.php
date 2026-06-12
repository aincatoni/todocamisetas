<?php

namespace Database\Seeders;

use App\Models\Camiseta;
use App\Models\Talla;
use Illuminate\Database\Seeder;

class CamisetaSeeder extends Seeder
{
    public function run(): void
    {
        $camiseta1 = Camiseta::updateOrCreate(
            ['codigo_producto' => 'CHI-LOC-2025-01'],
            [
                'titulo' => 'Camiseta Local 2025',
                'club' => 'Seleccion Chilena',
                'pais' => 'Chile',
                'tipo' => 'Local',
                'color' => 'Rojo',
                'precio' => 45000,
                'precio_oferta' => 39990,
                'detalles' => 'Tela dry-fit, version hincha.',
            ]
        );

        $camiseta2 = Camiseta::updateOrCreate(
            ['codigo_producto' => 'ARG-VIS-2025-01'],
            [
                'titulo' => 'Camiseta Visita 2025',
                'club' => 'Seleccion Argentina',
                'pais' => 'Argentina',
                'tipo' => 'Visita',
                'color' => 'Azul',
                'precio' => 47000,
                'precio_oferta' => null,
                'detalles' => 'Version oficial de visitante.',
            ]
        );

        $tallasBase = Talla::whereIn('nombre', ['S', 'M', 'L', 'XL'])->get()->keyBy('nombre');

        $camiseta1->tallas()->sync([
            $tallasBase['M']->id,
            $tallasBase['L']->id,
            $tallasBase['XL']->id,
        ]);

        $camiseta2->tallas()->sync([
            $tallasBase['S']->id,
            $tallasBase['M']->id,
            $tallasBase['L']->id,
        ]);
    }
}
