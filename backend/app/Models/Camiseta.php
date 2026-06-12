<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Camiseta extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'club',
        'pais',
        'tipo',
        'color',
        'precio',
        'precio_oferta',
        'detalles',
        'codigo_producto',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'precio_oferta' => 'decimal:2',
    ];

    public function tallas(): BelongsToMany
    {
        return $this->belongsToMany(Talla::class);
    }
}
