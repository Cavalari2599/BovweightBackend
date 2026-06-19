<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private User $tecnico;
    private User $ganadero;
    private string $tokenTecnico;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['id_rol' => 1, 'nombre_rol' => 'Ganadero'],
            ['id_rol' => 2, 'nombre_rol' => 'Veterinario'],
            ['id_rol' => 3, 'nombre_rol' => 'Ayudante'],
            ['id_rol' => 4, 'nombre_rol' => 'Tecnico'],
        ]);

        $this->tecnico = User::create([
            'identificacion_usuario' => 100004,
            'correo'                 => 'tecnico@test.com',
            'clave'                  => Hash::make('password123'),
            'id_rol'                 => 4,
            'estado'                 => true,
            'nombre_usuario'         => 'María',
            'apellido1_usuario'      => 'Vargas',
        ]);

        $this->ganadero = User::create([
            'identificacion_usuario' => 100001,
            'correo'                 => 'ganadero@test.com',
            'clave'                  => Hash::make('password123'),
            'id_rol'                 => 1,
            'estado'                 => true,
            'nombre_usuario'         => 'Carlos',
            'apellido1_usuario'      => 'Rodríguez',
        ]);

        $this->tokenTecnico = $this->tecnico->createToken('test')->plainTextToken;
    }

    // ─── INDEX ────────────────────────────────────────────────────────────────

    public function test_listar_usuarios_como_tecnico(): void
    {
        $response = $this->withToken($this->tokenTecnico)->getJson('/api/usuarios');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(2);
    }

    public function test_listar_usuarios_sin_rol_tecnico_da_403(): void
    {
        $token = $this->ganadero->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/usuarios');
        $response->assertStatus(403);
    }

    public function test_listar_usuarios_busqueda_por_nombre(): void
    {
        $response = $this->withToken($this->tokenTecnico)
                         ->getJson('/api/usuarios?buscar=Carlos');

        $response->assertStatus(200)
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.nombre_usuario', 'Carlos');
    }

    // ─── STORE ────────────────────────────────────────────────────────────────

    public function test_crear_usuario_exitoso(): void
    {
        $response = $this->withToken($this->tokenTecnico)->postJson('/api/usuarios', [
            'identificacion_usuario' => 200001,
            'correo'                 => 'nuevo@test.com',
            'clave'                  => 'password123',
            'id_rol'                 => 1,
            'nombre_usuario'         => 'Juan',
            'apellido1_usuario'      => 'Perez',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Usuario creado correctamente.')
                 ->assertJsonPath('usuario.correo', 'nuevo@test.com');

        $this->assertDatabaseHas('users', ['correo' => 'nuevo@test.com']);
    }

    public function test_crear_usuario_correo_duplicado_falla(): void
    {
        $response = $this->withToken($this->tokenTecnico)->postJson('/api/usuarios', [
            'identificacion_usuario' => 200002,
            'correo'                 => 'ganadero@test.com', // ya existe
            'clave'                  => 'password123',
            'id_rol'                 => 1,
            'nombre_usuario'         => 'Otro',
            'apellido1_usuario'      => 'Usuario',
        ]);

        $response->assertStatus(422);
    }

    public function test_crear_usuario_clave_muy_corta_falla(): void
    {
        $response = $this->withToken($this->tokenTecnico)->postJson('/api/usuarios', [
            'identificacion_usuario' => 200003,
            'correo'                 => 'otro@test.com',
            'clave'                  => '123', // menos de 8 chars
            'id_rol'                 => 1,
            'nombre_usuario'         => 'Otro',
            'apellido1_usuario'      => 'Usuario',
        ]);

        $response->assertStatus(422);
    }

    // ─── SHOW ─────────────────────────────────────────────────────────────────

    public function test_ver_usuario_por_id(): void
    {
        $response = $this->withToken($this->tokenTecnico)
                         ->getJson('/api/usuarios/100001');

        $response->assertStatus(200)
                 ->assertJsonPath('correo', 'ganadero@test.com');
    }

    public function test_ver_usuario_inexistente_retorna_404(): void
    {
        $response = $this->withToken($this->tokenTecnico)->getJson('/api/usuarios/999999');
        $response->assertStatus(404);
    }

    // ─── UPDATE ───────────────────────────────────────────────────────────────

    public function test_actualizar_usuario_nombre(): void
    {
        $response = $this->withToken($this->tokenTecnico)
                         ->putJson('/api/usuarios/100001', [
                             'nombre_usuario' => 'Carlos Actualizado',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('usuario.nombre_usuario', 'Carlos Actualizado');
    }

    public function test_actualizar_usuario_inexistente_retorna_404(): void
    {
        $response = $this->withToken($this->tokenTecnico)
                         ->putJson('/api/usuarios/999999', ['nombre_usuario' => 'X']);

        $response->assertStatus(404);
    }

    // ─── TOGGLE ESTADO ────────────────────────────────────────────────────────

    public function test_toggle_estado_desactiva_usuario_activo(): void
    {
        $response = $this->withToken($this->tokenTecnico)
                         ->patchJson('/api/usuarios/100001/estado');

        $response->assertStatus(200)
                 ->assertJsonPath('usuario.estado', false);
    }

    public function test_toggle_estado_activa_usuario_inactivo(): void
    {
        $this->ganadero->update(['estado' => false]);

        $response = $this->withToken($this->tokenTecnico)
                         ->patchJson('/api/usuarios/100001/estado');

        $response->assertStatus(200)
                 ->assertJsonPath('usuario.estado', true);
    }

    public function test_toggle_estado_usuario_inexistente_retorna_404(): void
    {
        $response = $this->withToken($this->tokenTecnico)
                         ->patchJson('/api/usuarios/999999/estado');

        $response->assertStatus(404);
    }

    // ─── ROLES ────────────────────────────────────────────────────────────────

    public function test_listar_roles_como_tecnico(): void
    {
        $response = $this->withToken($this->tokenTecnico)->getJson('/api/roles');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(4);
    }
}