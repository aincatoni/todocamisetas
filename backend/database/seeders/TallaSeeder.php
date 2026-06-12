<?php

namespace Database\Seeders;

use App\Models\Talla;
use Illuminate\Database\Seeder;

class TallaSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['S', 'M', 'L', 'XL'] as $nombre) {
            Talla::updateOrCreate(['nombre' => $nombre], ['nombre' => $nombre]);
        }
    }
}
