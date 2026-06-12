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
        Schema::create('camiseta_talla', function (Blueprint $table) {
            $table->id();
            $table->foreignId('camiseta_id')->constrained('camisetas')->cascadeOnDelete();
            $table->foreignId('talla_id')->constrained('tallas')->restrictOnDelete();
            $table->timestamps();

            $table->unique(['camiseta_id', 'talla_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('camiseta_talla');
    }
};
