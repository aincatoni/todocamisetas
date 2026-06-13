<?php

namespace Tests\Feature;

use App\Models\Camiseta;
use App\Models\Cliente;
use App\Models\Talla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CamisetaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_filter_camisetas_by_club(): void
    {
        Camiseta::create([
            'titulo' => 'Camiseta Local 2025',
            'club' => 'Seleccion Chilena',
            'pais' => 'Chile',
            'tipo' => 'Local',
            'color' => 'Rojo',
            'precio' => 45000,
            'precio_oferta' => 39990,
            'detalles' => 'Principal',
            'codigo_producto' => 'CHI-LOC-2025-01',
        ]);

        Camiseta::create([
            'titulo' => 'Camiseta Visita 2025',
            'club' => 'Seleccion Argentina',
            'pais' => 'Argentina',
            'tipo' => 'Visita',
            'color' => 'Azul',
            'precio' => 47000,
            'precio_oferta' => null,
            'detalles' => 'Secundaria',
            'codigo_producto' => 'ARG-VIS-2025-01',
        ]);

        $response = $this->getJson('/api/camisetas?club=Seleccion Chilena');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.club', 'Seleccion Chilena');
    }

    public function test_can_create_update_and_delete_camiseta(): void
    {
        $cliente = Cliente::create([
            'nombre_comercial' => 'deportes_total',
            'rut' => '77111222-3',
            'direccion' => 'Av. Grecia 123, Nunoa',
            'categoria' => 'Preferencial',
            'contacto_nombre' => 'Ana Torres',
            'contacto_email' => 'compras@deportestotal.cl',
            'porcentaje_oferta' => 15,
        ]);

        $payload = [
            'titulo' => 'Camiseta Alternativa 2025',
            'club' => 'Universidad de Chile',
            'pais' => 'Chile',
            'tipo' => 'Alternativa',
            'color' => 'Azul',
            'precio' => 52990,
            'precio_oferta' => 47990,
            'detalles' => 'Version jugador manga corta.',
            'codigo_producto' => 'UCH-ALT-2025-01',
            'cliente_id' => $cliente->id,
        ];

        $created = $this->postJson('/api/camisetas', $payload);

        $created->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.codigo_producto', $payload['codigo_producto']);

        $camisetaId = $created->json('data.id');

        $updated = $this->putJson("/api/camisetas/{$camisetaId}", [
            'color' => 'Azul Marino',
            'precio' => 54990,
            'precio_oferta' => 49990,
        ]);

        $updated->assertOk()
            ->assertJsonPath('data.color', 'Azul Marino')
            ->assertJsonPath('data.precio', '54990.00')
            ->assertJsonPath('data.cliente_id', $cliente->id);

        $deleted = $this->deleteJson("/api/camisetas/{$camisetaId}");

        $deleted->assertOk()
            ->assertJsonPath('data.message', 'Camiseta eliminada exitosamente.');

        $this->assertDatabaseMissing('camisetas', [
            'id' => $camisetaId,
        ]);
    }

    public function test_show_camiseta_calculates_precio_final_for_preferential_cliente(): void
    {
        $cliente = Cliente::create([
            'nombre_comercial' => '90minutos',
            'rut' => '76123456-7',
            'direccion' => 'Providencia, Santiago',
            'categoria' => 'Preferencial',
            'contacto_nombre' => 'Carla Paredes',
            'contacto_email' => 'compras@90minutos.cl',
            'porcentaje_oferta' => 10,
        ]);

        $camiseta = Camiseta::create([
            'titulo' => 'Camiseta Local 2025',
            'club' => 'Seleccion Chilena',
            'pais' => 'Chile',
            'tipo' => 'Local',
            'color' => 'Rojo',
            'precio' => 45000,
            'precio_oferta' => 39990,
            'detalles' => 'Tela dry-fit',
            'codigo_producto' => 'CHI-LOC-2025-01',
        ]);

        $talla = Talla::create(['nombre' => 'M']);
        $camiseta->tallas()->attach($talla->id);

        $response = $this->getJson("/api/camisetas/{$camiseta->id}?cliente_id={$cliente->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.precio_final', '39990.00')
            ->assertJsonPath('data.cliente_consultado.id', $cliente->id)
            ->assertJsonPath('data.tallas.0.nombre', 'M');
    }

    public function test_show_camiseta_returns_404_when_cliente_query_does_not_exist(): void
    {
        $camiseta = Camiseta::create([
            'titulo' => 'Camiseta Local 2025',
            'club' => 'Seleccion Chilena',
            'pais' => 'Chile',
            'tipo' => 'Local',
            'color' => 'Rojo',
            'precio' => 45000,
            'precio_oferta' => 39990,
            'detalles' => 'Tela dry-fit',
            'codigo_producto' => 'CHI-LOC-2025-01',
        ]);

        $response = $this->getJson("/api/camisetas/{$camiseta->id}?cliente_id=99999");

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Cliente no encontrado.',
            ]);
    }

    public function test_can_list_camisetas_by_cliente(): void
    {
        $cliente = Cliente::create([
            'nombre_comercial' => 'cliente_con_camisetas',
            'rut' => '76123456-8',
            'direccion' => 'Providencia, Santiago',
            'categoria' => 'Regular',
            'contacto_nombre' => 'Carla Paredes',
            'contacto_email' => 'cliente@camisetas.cl',
            'porcentaje_oferta' => 0,
        ]);

        Camiseta::create([
            'titulo' => 'Camiseta Local 2025',
            'club' => 'Seleccion Chilena',
            'pais' => 'Chile',
            'tipo' => 'Local',
            'color' => 'Rojo',
            'precio' => 45000,
            'precio_oferta' => 39990,
            'detalles' => 'Principal',
            'codigo_producto' => 'CHI-LOC-2025-01',
            'cliente_id' => $cliente->id,
        ]);

        Camiseta::create([
            'titulo' => 'Camiseta Visita 2025',
            'club' => 'Seleccion Argentina',
            'pais' => 'Argentina',
            'tipo' => 'Visita',
            'color' => 'Azul',
            'precio' => 47000,
            'precio_oferta' => null,
            'detalles' => 'Secundaria',
            'codigo_producto' => 'ARG-VIS-2025-01',
        ]);

        $response = $this->getJson("/api/clientes/{$cliente->id}/camisetas");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.cliente_id', $cliente->id)
            ->assertJsonPath('data.0.codigo_producto', 'CHI-LOC-2025-01');
    }
}
