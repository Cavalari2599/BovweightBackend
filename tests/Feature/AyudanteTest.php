<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rol;
use App\Models\Finca;
use App\Models\Animal;
use App\Models\Ayudante;
use App\Models\Pesaje;
use App\Models\Tratamiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AyudanteTest extends TestCase
{
    use RefreshDatabase;

    private User $ganadero;
    private User $ayudanteUser;
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

        $this->ayudanteUser = User::create([
            'identificacion_usuario' => 100003,
            'correo'                 => 'ayudante@test.com',
            'clave'                  => Hash::make('password123'),
            'id_rol'                 => 3,
            'estado'                 => true,
            'nombre_usuario'         => 'Luis',
            'apellido1_usuario'      => 'Jiménez',
        ]);

        $this->finca = Finca::create([
            'nombre_finca'           => 'Finca Test',
            'ubicacion_finca'        => 'Liberia',
            'identificacion_usuario' => 100001,
        ]);

        Ayudante::create([
            'identificacion_usuario' => 100003,
            'id_finca'               => $this->finca->id_finca,
        ]);

        $this->animal = Animal::create([
            'n_arete'       => 'ARETE-001',
            'nombre_animal' => 'Tormenta',
            'estado'        => 'Activo',
            'id_finca'      => $this->finca->id_finca,
            'peso'          => 300,
        ]);

        $this->token = $this->ayudanteUser->createToken('test')->plainTextToken;
    }

    // ─── FINCA ────────────────────────────────────────────────────────────────

    public function test_ayudante_obtiene_su_finca(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/ayudante/finca');
        $response->assertStatus(200);
    }

    // ─── ANIMALES ─────────────────────────────────────────────────────────────

    public function test_ayudante_obtiene_animales_de_su_finca(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/ayudante/animales');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1);
    }

    public function test_ayudante_obtiene_animales_ordenados_por_nombre(): void
    {
        // Abeja < Tormenta alfabéticamente
        Animal::create([
            'n_arete'       => 'A02',
            'nombre_animal' => 'Abeja',
            'estado'        => 'Activo',
            'id_finca'      => $this->finca->id_finca,
            'peso'          => 100,
        ]);

        $response = $this->withToken($this->token)->getJson('/api/ayudante/animales?ordenar=nombre');

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertCount(2, $data);
        $this->assertEquals('Abeja',    $data[0]['nombre_animal']);
        $this->assertEquals('Tormenta', $data[1]['nombre_animal']);
    }

    public function test_ayudante_obtiene_un_animal_por_arete(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/ayudante/animales/ARETE-001');

        $response->assertStatus(200)
                 ->assertJsonPath('nombre_animal', 'Tormenta');
    }

    // ─── PESAJES ──────────────────────────────────────────────────────────────

    public function test_ayudante_obtiene_pesajes_de_animal(): void
    {
        Pesaje::create([
            'n_arete'         => 'ARETE-001',
            'peso_aproximado' => 310,
            'fecha_pesaje'    => now(),
        ]);

        $response = $this->withToken($this->token)
                         ->getJson('/api/ayudante/animales/ARETE-001/pesajes');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1);
    }

    public function test_ayudante_crea_pesaje_manual(): void
    {
        $response = $this->withToken($this->token)
                         ->postJson('/api/ayudante/animales/ARETE-001/pesajes', [
                             'peso_aproximado' => 315.0,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Pesaje registrado correctamente.');

        $this->assertDatabaseHas('pesajes', ['n_arete' => 'ARETE-001', 'peso_aproximado' => 315.0]);
    }

    public function test_ayudante_crear_pesaje_peso_cero_falla(): void
    {
        $response = $this->withToken($this->token)
                         ->postJson('/api/ayudante/animales/ARETE-001/pesajes', [
                             'peso_aproximado' => 0,
                         ]);

        $response->assertStatus(422);
    }

    // ─── TRATAMIENTOS ─────────────────────────────────────────────────────────

    public function test_ayudante_obtiene_tratamientos_de_animal(): void
    {
        Tratamiento::create([
            'tipo_tratamiento'       => 'Vacuna',
            'fecha_inicio'           => now()->format('Y-m-d'),
            'n_arete'                => 'ARETE-001',
            'identificacion_usuario' => 100001,
        ]);

        $response = $this->withToken($this->token)
                         ->getJson('/api/ayudante/animales/ARETE-001/tratamientos');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1);
    }

    // ─── CONTROL DE ACCESO ────────────────────────────────────────────────────

    public function test_rutas_ayudante_requieren_autenticacion(): void
    {
        $response = $this->getJson('/api/ayudante/finca');
        $response->assertStatus(401);
    }

    public function test_ganadero_no_puede_acceder_rutas_ayudante(): void
    {
        $token = $this->ganadero->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/ayudante/finca');
        $response->assertStatus(403);
    }
}