<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial');
            $table->string('rut', 20)->unique();
            $table->string('direccion');
            $table->string('categoria', 20);
            $table->string('contacto_nombre');
            $table->string('contacto_email');
            $table->decimal('porcentaje_oferta', 5, 2)->nullable();
            $table->timestamps();

            $table->index('categoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
