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
        Schema::table('camisetas', function (Blueprint $table) {
            $table->foreignId('cliente_id')
                ->nullable()
                ->after('codigo_producto')
                ->constrained('clientes')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('camisetas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cliente_id');
        });
    }
};
