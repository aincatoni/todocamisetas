<?php

namespace Tests\Feature;

use App\Models\Camiseta;
use App\Models\Talla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TallaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_seeded_tallas(): void
    {
        $this->seed();

        $response = $this->getJson('/api/tallas');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(4, 'data')
            ->assertJsonFragment(['nombre' => 'S'])
            ->assertJsonFragment(['nombre' => 'XL']);
    }

    public function test_can_attach_and_detach_talla_to_camiseta(): void
    {
        $camiseta = Camiseta::create([
            'titulo' => 'Camiseta Alternativa 2025',
            'club' => 'Universidad de Chile',
            'pais' => 'Chile',
            'tipo' => 'Alternativa',
            'color' => 'Azul',
            'precio' => 52990,
            'precio_oferta' => 47990,
            'detalles' => 'Version jugador',
            'codigo_producto' => 'UCH-ALT-2025-01',
        ]);

        $talla = Talla::create(['nombre' => 'XXL']);

        $attached = $this->postJson("/api/camisetas/{$camiseta->id}/tallas", [
            'talla_id' => $talla->id,
        ]);

        $attached->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['nombre' => 'XXL']);

        $this->assertDatabaseHas('camiseta_talla', [
            'camiseta_id' => $camiseta->id,
            'talla_id' => $talla->id,
        ]);

        $detached = $this->deleteJson("/api/camisetas/{$camiseta->id}/tallas/{$talla->id}");

        $detached->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('camiseta_talla', [
            'camiseta_id' => $camiseta->id,
            'talla_id' => $talla->id,
        ]);
    }

    public function test_cannot_delete_talla_associated_to_camiseta(): void
    {
        $camiseta = Camiseta::create([
            'titulo' => 'Camiseta Alternativa 2025',
            'club' => 'Universidad de Chile',
            'pais' => 'Chile',
            'tipo' => 'Alternativa',
            'color' => 'Azul',
            'precio' => 52990,
            'precio_oferta' => 47990,
            'detalles' => 'Version jugador',
            'codigo_producto' => 'UCH-ALT-2025-01',
        ]);

        $talla = Talla::create(['nombre' => 'XXL']);
        $camiseta->tallas()->attach($talla->id);

        $response = $this->deleteJson("/api/tallas/{$talla->id}");

        $response->assertStatus(409)
            ->assertJson([
                'success' => false,
                'message' => 'No se puede eliminar una talla asociada a camisetas.',
            ]);
    }

    public function test_create_talla_returns_validation_errors(): void
    {
        $response = $this->postJson('/api/tallas', []);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Los datos enviados no son validos.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['nombre'],
            ]);
    }
}
