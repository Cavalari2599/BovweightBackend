<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class MiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private int $uidCounter = 300000;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['id_rol' => 1, 'nombre_rol' => 'Ganadero'],
            ['id_rol' => 2, 'nombre_rol' => 'Veterinario'],
            ['id_rol' => 3, 'nombre_rol' => 'Ayudante'],
            ['id_rol' => 4, 'nombre_rol' => 'Tecnico'],
        ]);
    }

    private function crearUser(int $idRol): User
    {
        $uid = $this->uidCounter++;
        return User::create([
            'identificacion_usuario' => $uid,
            'correo'                 => "user{$uid}@test.com",
            'clave'                  => Hash::make('pass'),
            'id_rol'                 => $idRol,
            'estado'                 => true,
            'nombre_usuario'         => 'Test',
            'apellido1_usuario'      => 'User',
        ]);
    }

    public function test_ganadero_accede_a_ruta_ganadero(): void
    {
        $token = $this->crearUser(1)->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/ganadero/fincas');

        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(401, $response->status());
    }

    public function test_tecnico_accede_a_ruta_tecnico(): void
    {
        $token = $this->crearUser(4)->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/usuarios');

        $this->assertNotEquals(403, $response->status());
        $this->assertNotEquals(401, $response->status());
    }

    public function test_ayudante_no_puede_acceder_ruta_ganadero(): void
    {
        $token = $this->crearUser(3)->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/ganadero/fincas');

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'No tienes permisos para realizar esta acción.');
    }

    public function test_veterinario_no_puede_acceder_ruta_tecnico(): void
    {
        $token = $this->crearUser(2)->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/usuarios');

        $response->assertStatus(403);
    }

    public function test_ganadero_no_puede_acceder_ruta_veterinario(): void
    {
        $token = $this->crearUser(1)->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/veterinario/fincas');

        $response->assertStatus(403);
    }

    public function test_ayudante_no_puede_acceder_ruta_veterinario(): void
    {
        $token = $this->crearUser(3)->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/veterinario/fincas');

        $response->assertStatus(403);
    }

    public function test_sin_autenticar_ruta_ganadero_retorna_401(): void
    {
        $response = $this->getJson('/api/ganadero/fincas');
        $response->assertStatus(401);
    }

    public function test_sin_autenticar_ruta_veterinario_retorna_401(): void
    {
        $response = $this->getJson('/api/veterinario/fincas');
        $response->assertStatus(401);
    }

    public function test_sin_autenticar_ruta_ayudante_retorna_401(): void
    {
        $response = $this->getJson('/api/ayudante/finca');
        $response->assertStatus(401);
    }

    public function test_sin_autenticar_ruta_tecnico_retorna_401(): void
    {
        $response = $this->getJson('/api/usuarios');
        $response->assertStatus(401);
    }
}