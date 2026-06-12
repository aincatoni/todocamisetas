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
        Schema::create('camisetas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->string('club');
            $table->string('pais');
            $table->string('tipo', 100);
            $table->string('color', 100);
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_oferta', 10, 2)->nullable();
            $table->text('detalles')->nullable();
            $table->string('codigo_producto', 100)->unique();
            $table->timestamps();

            $table->index(['club', 'pais']);
            $table->index('tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camisetas');
    }
};
