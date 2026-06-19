<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rol;
use App\Models\Finca;
use App\Models\Animal;
use App\Models\Atiende;
use App\Models\Tratamiento;
use App\Models\Pesaje;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class VeterinarioTest extends TestCase
{
    use RefreshDatabase;

    private User $veterinario;
    private User $ganadero;
    private Finca $finca;
    private Animal $animal;
    private string $token;

    protected function setUp(): void
    {
        parent::setUp();

        Rol::insert([
            ['id_rol' => 1, 'nombre_rol' => 'Ganadero'],
            ['id_rol' => 2, 'nombre_rol' => 'Veterinario'],
            ['id_rol' => 3, 'nombre_rol' => 'Ayudante'],
            ['id_rol' => 4, 'nombre_rol' => 'Tecnico'],
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

        $this->veterinario = User::create([
            'identificacion_usuario' => 100002,
            'correo'                 => 'vet@test.com',
            'clave'                  => Hash::make('password123'),
            'id_rol'                 => 2,
            'estado'                 => true,
            'nombre_usuario'         => 'Ana',
            'apellido1_usuario'      => 'González',
        ]);

        $this->finca = Finca::create([
            'nombre_finca'           => 'Finca Test',
            'ubicacion_finca'        => 'Liberia',
            'identificacion_usuario' => 100001,
        ]);

        $this->animal = Animal::create([
            'n_arete'  => 'ARETE-001',
            'estado'   => 'Activo',
            'id_finca' => $this->finca->id_finca,
            'peso'     => 300,
        ]);

        // Asignar veterinario a la finca
        Atiende::create([
            'identificacion_usuario' => 100002,
            'id_finca'               => $this->finca->id_finca,
        ]);

        $this->token = $this->veterinario->createToken('test')->plainTextToken;
    }

    // ─── FINCAS ───────────────────────────────────────────────────────────────

    public function test_veterinario_obtiene_fincas_asignadas(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/veterinario/fincas');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.nombre_finca', 'Finca Test');
    }

    public function test_veterinario_sin_fincas_asignadas_retorna_lista_vacia(): void
    {
        $otroVet = User::create([
            'identificacion_usuario' => 100005,
            'correo'                 => 'vet2@test.com',
            'clave'                  => Hash::make('password123'),
            'id_rol'                 => 2,
            'estado'                 => true,
            'nombre_usuario'         => 'Pedro',
            'apellido1_usuario'      => 'López',
        ]);

        $token = $otroVet->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/veterinario/fincas');

        $response->assertStatus(200)->assertJsonCount(0);
    }

    // ─── ANIMALES ─────────────────────────────────────────────────────────────

    public function test_veterinario_obtiene_animales_de_finca_asignada(): void
    {
        $response = $this->withToken($this->token)
                         ->getJson("/api/veterinario/fincas/{$this->finca->id_finca}/animales");

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1);
    }

    public function test_veterinario_no_puede_ver_animales_de_finca_no_asignada(): void
    {
        $otraFinca = Finca::create([
            'nombre_finca'           => 'Otra Finca',
            'ubicacion_finca'        => 'Alajuela',
            'identificacion_usuario' => 100001,
        ]);

        $response = $this->withToken($this->token)
                         ->getJson("/api/veterinario/fincas/{$otraFinca->id_finca}/animales");

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'No tienes acceso a esta finca.');
    }

    // ─── TRATAMIENTOS ─────────────────────────────────────────────────────────

    public function test_obtener_tratamientos_de_animal(): void
    {
        Tratamiento::create([
            'tipo_tratamiento'       => 'Vacuna',
            'fecha_inicio'           => now()->format('Y-m-d'),
            'n_arete'                => 'ARETE-001',
            'identificacion_usuario' => 100002,
        ]);

        $response = $this->withToken($this->token)
                         ->getJson('/api/veterinario/animales/ARETE-001/tratamientos');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.tipo_tratamiento', 'Vacuna');
    }

    public function test_crear_tratamiento_exitoso(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/veterinario/tratamientos', [
            'tipo_tratamiento' => 'Antibiótico',
            'medicamento'      => 'Penicilina',
            'descripcion'      => 'Infección leve',
            'fecha_inicio'     => now()->format('Y-m-d'),
            'n_arete'          => 'ARETE-001',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Tratamiento registrado correctamente.')
                 ->assertJsonPath('tratamiento.tipo_tratamiento', 'Antibiótico');

        $this->assertDatabaseHas('tratamientos', ['n_arete' => 'ARETE-001', 'tipo_tratamiento' => 'Antibiótico']);
    }

    public function test_crear_tratamiento_sin_tipo_falla(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/veterinario/tratamientos', [
            'fecha_inicio' => now()->format('Y-m-d'),
            'n_arete'      => 'ARETE-001',
        ]);

        $response->assertStatus(422);
    }

    public function test_crear_tratamiento_arete_inexistente_falla(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/veterinario/tratamientos', [
            'tipo_tratamiento' => 'Vacuna',
            'fecha_inicio'     => now()->format('Y-m-d'),
            'n_arete'          => 'NO-EXISTE',
        ]);

        $response->assertStatus(422);
    }

    public function test_actualizar_tratamiento_exitoso(): void
    {
        $tratamiento = Tratamiento::create([
            'tipo_tratamiento'       => 'Vacuna',
            'fecha_inicio'           => now()->format('Y-m-d'),
            'n_arete'                => 'ARETE-001',
            'identificacion_usuario' => 100002,
        ]);

        $response = $this->withToken($this->token)
                         ->putJson("/api/veterinario/tratamientos/{$tratamiento->id_tratamiento}", [
                             'medicamento' => 'Ivermectina',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('tratamiento.medicamento', 'Ivermectina');
    }

    public function test_actualizar_tratamiento_inexistente_retorna_404(): void
    {
        $response = $this->withToken($this->token)
                         ->putJson('/api/veterinario/tratamientos/999', [
                             'medicamento' => 'Algo',
                         ]);

        $response->assertStatus(404)
                 ->assertJsonPath('message', 'Tratamiento no encontrado.');
    }

    // ─── PESAJES ──────────────────────────────────────────────────────────────

    public function test_veterinario_obtiene_pesajes_de_animal(): void
    {
        Pesaje::create([
            'n_arete'         => 'ARETE-001',
            'peso_aproximado' => 320,
            'fecha_pesaje'    => now(),
        ]);

        $response = $this->withToken($this->token)
                         ->getJson('/api/veterinario/animales/ARETE-001/pesajes');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1);
    }

    // ─── CONTROL DE ACCESO ────────────────────────────────────────────────────

    public function test_rutas_veterinario_sin_autenticacion_retorna_401(): void
    {
        $response = $this->getJson('/api/veterinario/fincas');
        $response->assertStatus(401);
    }

    public function test_ganadero_no_puede_acceder_rutas_veterinario(): void
    {
        $token = $this->ganadero->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/veterinario/fincas');
        $response->assertStatus(403);
    }
}