<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClienteApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_seeded_clientes(): void
    {
        $this->seed();

        $response = $this->getJson('/api/clientes');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['nombre_comercial' => '90minutos'])
            ->assertJsonFragment(['nombre_comercial' => 'tdeportes']);
    }

    public function test_can_create_cliente(): void
    {
        $payload = [
            'nombre_comercial' => 'deportes_total',
            'rut' => '77111222-3',
            'direccion' => 'Av. Grecia 123, Nunoa',
            'categoria' => 'Preferencial',
            'contacto_nombre' => 'Ana Torres',
            'contacto_email' => 'compras@deportestotal.cl',
            'porcentaje_oferta' => 15,
        ];

        $response = $this->postJson('/api/clientes', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nombre_comercial', $payload['nombre_comercial'])
            ->assertJsonPath('data.rut', $payload['rut']);

        $this->assertDatabaseHas('clientes', [
            'rut' => $payload['rut'],
            'contacto_email' => $payload['contacto_email'],
        ]);
    }

    public function test_create_cliente_returns_validation_errors(): void
    {
        $response = $this->postJson('/api/clientes', [
            'rut' => '77111222-3',
            'contacto_email' => 'correo-invalido',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Los datos enviados no son validos.')
            ->assertJsonStructure([
                'success',
                'message',
                'errors' => ['nombre_comercial', 'direccion', 'categoria', 'contacto_nombre', 'contacto_email'],
            ]);
    }

    public function test_can_update_cliente(): void
    {
        $cliente = Cliente::create([
            'nombre_comercial' => 'cliente_base',
            'rut' => '76123456-7',
            'direccion' => 'Providencia, Santiago',
            'categoria' => 'Regular',
            'contacto_nombre' => 'Base Contacto',
            'contacto_email' => 'base@cliente.cl',
            'porcentaje_oferta' => 0,
        ]);

        $response = $this->putJson("/api/clientes/{$cliente->id}", [
            'direccion' => 'Las Condes, Santiago',
            'categoria' => 'Preferencial',
            'contacto_nombre' => 'Contacto Actualizado',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.categoria', 'Preferencial')
            ->assertJsonPath('data.contacto_nombre', 'Contacto Actualizado');

        $this->assertDatabaseHas('clientes', [
            'id' => $cliente->id,
            'direccion' => 'Las Condes, Santiago',
            'categoria' => 'Preferencial',
        ]);
    }

    public function test_can_delete_cliente(): void
    {
        $cliente = Cliente::create([
            'nombre_comercial' => 'cliente_base',
            'rut' => '76123456-7',
            'direccion' => 'Providencia, Santiago',
            'categoria' => 'Regular',
            'contacto_nombre' => 'Base Contacto',
            'contacto_email' => 'base@cliente.cl',
            'porcentaje_oferta' => 0,
        ]);

        $response = $this->deleteJson("/api/clientes/{$cliente->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.message', 'Cliente eliminado exitosamente.');

        $this->assertDatabaseMissing('clientes', [
            'id' => $cliente->id,
        ]);
    }
}
