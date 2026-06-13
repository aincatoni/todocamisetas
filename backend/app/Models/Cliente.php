<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cliente extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_comercial',
        'rut',
        'direccion',
        'categoria',
        'contacto_nombre',
        'contacto_email',
        'porcentaje_oferta',
    ];

    protected $casts = [
        'porcentaje_oferta' => 'decimal:2',
    ];

    public function camisetas(): HasMany
    {
        return $this->hasMany(Camiseta::class);
    }
}
