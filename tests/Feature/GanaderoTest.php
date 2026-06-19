<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rol;
use App\Models\Finca;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Atiende;
use App\Models\Ayudante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class GanaderoTest extends TestCase
{
    use RefreshDatabase;

    private User $ganadero;
    private User $veterinario;
    private User $ayudante;
    private Finca $finca;
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

        $this->ayudante = User::create([
            'identificacion_usuario' => 100003,
            'correo'                 => 'ayudante@test.com',
            'clave'                  => Hash::make('password123'),
            'id_rol'                 => 3,
            'estado'                 => true,
            'nombre_usuario'         => 'Luis',
            'apellido1_usuario'      => 'Jiménez',
        ]);

        $this->finca = Finca::create([
            'nombre_finca'           => 'Finca La Esperanza',
            'ubicacion_finca'        => 'Liberia, Guanacaste',
            'identificacion_usuario' => 100001,
        ]);

        $this->token = $this->ganadero->createToken('test')->plainTextToken;
    }

    // ─── FINCAS ───────────────────────────────────────────────────────────────

    public function test_ganadero_obtiene_sus_fincas(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/ganadero/fincas');

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1)
                 ->assertJsonPath('0.nombre_finca', 'Finca La Esperanza');
    }

    public function test_crear_finca_exitoso(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/ganadero/fincas', [
            'nombre_finca'    => 'Nueva Finca',
            'ubicacion_finca' => 'San José, Costa Rica',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Finca creada correctamente.')
                 ->assertJsonPath('finca.nombre_finca', 'Nueva Finca');

        $this->assertDatabaseHas('fincas', ['nombre_finca' => 'Nueva Finca']);
    }

    public function test_crear_finca_sin_nombre_falla(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/ganadero/fincas', [
            'ubicacion_finca' => 'San José',
        ]);

        $response->assertStatus(422);
    }

    public function test_editar_finca_exitoso(): void
    {
        $response = $this->withToken($this->token)
                         ->putJson("/api/ganadero/fincas/{$this->finca->id_finca}", [
                             'nombre_finca' => 'Finca Actualizada',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('finca.nombre_finca', 'Finca Actualizada');
    }

    // ─── ANIMALES ─────────────────────────────────────────────────────────────

    public function test_obtener_animales_de_finca(): void
    {
        Animal::create([
            'n_arete'    => 'ARETE-001',
            'estado'     => 'Activo',
            'id_finca'   => $this->finca->id_finca,
            'peso'       => 300.00,
        ]);

        $response = $this->withToken($this->token)
                         ->getJson("/api/ganadero/fincas/{$this->finca->id_finca}/animales");

        $response->assertStatus(200)
                 ->assertJsonIsArray()
                 ->assertJsonCount(1);
    }

    public function test_obtener_animales_ordenar_por_nombre(): void
    {
        Animal::create(['n_arete' => 'A01', 'nombre_animal' => 'Zorro', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca, 'peso' => 100]);
        Animal::create(['n_arete' => 'A02', 'nombre_animal' => 'Abeja', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca, 'peso' => 200]);

        $response = $this->withToken($this->token)
                         ->getJson("/api/ganadero/fincas/{$this->finca->id_finca}/animales?ordenar=nombre");

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals('Abeja', $data[0]['nombre_animal']);
        $this->assertEquals('Zorro', $data[1]['nombre_animal']);
    }

    public function test_crear_animal_exitoso(): void
    {
        $response = $this->withToken($this->token)
                         ->postJson("/api/ganadero/fincas/{$this->finca->id_finca}/animales", [
                             'n_arete' => 'ARETE-100',
                             'estado'  => 'Activo',
                             'peso'    => 250.0,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Animal creado correctamente.');

        $this->assertDatabaseHas('animales', ['n_arete' => 'ARETE-100']);
    }

    public function test_crear_animal_arete_duplicado_falla(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->postJson("/api/ganadero/fincas/{$this->finca->id_finca}/animales", [
                             'n_arete' => 'ARETE-001',
                             'estado'  => 'Activo',
                         ]);

        $response->assertStatus(422);
    }

    public function test_editar_animal_exitoso(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca, 'peso' => 300]);

        $response = $this->withToken($this->token)
                         ->putJson('/api/ganadero/animales/ARETE-001', [
                             'nombre_animal' => 'Tormenta Actualizada',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('animal.nombre_animal', 'Tormenta Actualizada');
    }

    public function test_obtener_animal_por_arete(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'nombre_animal' => 'Tormenta', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)->getJson('/api/ganadero/animales/ARETE-001');

        $response->assertStatus(200)
                 ->assertJsonPath('nombre_animal', 'Tormenta');
    }

    public function test_todos_los_animales_del_ganadero(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)->getJson('/api/ganadero/animales-todos');

        $response->assertStatus(200)->assertJsonIsArray();
    }

    // ─── PESAJES ──────────────────────────────────────────────────────────────

    public function test_crear_pesaje_manual(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->postJson('/api/ganadero/animales/ARETE-001/pesajes', [
                             'peso_aproximado' => 350.5,
                         ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Pesaje registrado correctamente.');

        $this->assertDatabaseHas('pesajes', ['n_arete' => 'ARETE-001', 'peso_aproximado' => 350.5]);
    }

    public function test_crear_pesaje_peso_invalido_falla(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->postJson('/api/ganadero/animales/ARETE-001/pesajes', [
                             'peso_aproximado' => 0, // min:1
                         ]);

        $response->assertStatus(422);
    }

    public function test_obtener_pesajes_de_animal(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);
        Pesaje::create(['n_arete' => 'ARETE-001', 'peso_aproximado' => 300, 'fecha_pesaje' => now()]);

        $response = $this->withToken($this->token)
                         ->getJson('/api/ganadero/animales/ARETE-001/pesajes');

        $response->assertStatus(200)->assertJsonCount(1);
    }

    // ─── PROGRAMAR PESAJE ─────────────────────────────────────────────────────

    public function test_programar_pesaje_exitoso(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->putJson('/api/ganadero/animales/ARETE-001/programar', [
                             'proximo_pesaje'    => now()->addDays(30)->format('Y-m-d'),
                             'repetir_cada_dias' => 30,
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Recordatorio actualizado.');
    }

    public function test_programar_pesaje_dias_invalidos_falla(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->putJson('/api/ganadero/animales/ARETE-001/programar', [
                             'repetir_cada_dias' => 45, // no está en: 7,15,30,60,90
                         ]);

        $response->assertStatus(422);
    }

    public function test_recordatorios_del_ganadero(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/ganadero/recordatorios');
        $response->assertStatus(200)->assertJsonIsArray();
    }

    // ─── RESUMEN FINCA ────────────────────────────────────────────────────────

    public function test_resumen_finca(): void
    {
        $response = $this->withToken($this->token)
                         ->getJson("/api/ganadero/fincas/{$this->finca->id_finca}/resumen");

        $response->assertStatus(200);
    }

    // ─── ASIGNAR / DESASIGNAR VETERINARIO ────────────────────────────────────

    public function test_asignar_veterinario_a_finca(): void
    {
        $response = $this->withToken($this->token)
                         ->postJson("/api/ganadero/fincas/{$this->finca->id_finca}/veterinarios", [
                             'id_veterinario' => 100002,
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Veterinario asignado correctamente.');

        $this->assertDatabaseHas('atiende', [
            'id_finca'               => $this->finca->id_finca,
            'identificacion_usuario' => 100002,
        ]);
    }

    public function test_desasignar_veterinario_de_finca(): void
    {
        Atiende::create(['identificacion_usuario' => 100002, 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->deleteJson("/api/ganadero/fincas/{$this->finca->id_finca}/veterinarios", [
                             'id_veterinario' => 100002,
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Veterinario desasignado correctamente.');
    }

    public function test_obtener_veterinarios_asignados_a_finca(): void
    {
        Atiende::create(['identificacion_usuario' => 100002, 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->getJson("/api/ganadero/fincas/{$this->finca->id_finca}/veterinarios");

        $response->assertStatus(200)->assertJsonIsArray();
    }

    // ─── ASIGNAR / DESASIGNAR AYUDANTE ───────────────────────────────────────

    public function test_asignar_ayudante_a_finca(): void
    {
        $response = $this->withToken($this->token)
                         ->postJson("/api/ganadero/fincas/{$this->finca->id_finca}/ayudantes", [
                             'id_ayudante' => 100003,
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Ayudante asignado correctamente.');
    }

    public function test_desasignar_ayudante(): void
    {
        Ayudante::create(['identificacion_usuario' => 100003, 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->deleteJson('/api/ganadero/ayudantes', [
                             'id_ayudante' => 100003,
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Ayudante desasignado correctamente.');
    }

    public function test_obtener_ayudantes_asignados_a_finca(): void
    {
        $response = $this->withToken($this->token)
                         ->getJson("/api/ganadero/fincas/{$this->finca->id_finca}/ayudantes");

        $response->assertStatus(200)->assertJsonIsArray();
    }

    // ─── LISTAR VETERINARIOS Y AYUDANTES ─────────────────────────────────────

    public function test_listar_veterinarios_disponibles(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/ganadero/veterinarios');
        $response->assertStatus(200)->assertJsonIsArray();
    }

    public function test_listar_ayudantes_disponibles(): void
    {
        $response = $this->withToken($this->token)->getJson('/api/ganadero/ayudantes');
        $response->assertStatus(200)->assertJsonIsArray();
    }

    //  TRATAMIENTOS ────────────────

    public function test_obtener_tratamientos_de_animal(): void
    {
        Animal::create(['n_arete' => 'ARETE-001', 'estado' => 'Activo', 'id_finca' => $this->finca->id_finca]);

        $response = $this->withToken($this->token)
                         ->getJson('/api/ganadero/animales/ARETE-001/tratamientos');

        $response->assertStatus(200)->assertJsonIsArray();
    }

    // ─── CONTROL DE ACCESO ────────────────────────────────────────────────────

    public function test_rutas_ganadero_requieren_autenticacion(): void
    {
        $response = $this->getJson('/api/ganadero/fincas');
        $response->assertStatus(401);
    }

    public function test_veterinario_no_puede_acceder_rutas_ganadero(): void
    {
        $token = $this->veterinario->createToken('test')->plainTextToken;
        $response = $this->withToken($token)->getJson('/api/ganadero/fincas');
        $response->assertStatus(403);
    }

    // ─── REPORTE ──────────────────────────────────────────────────────────────

    public function test_registrar_reporte(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/ganadero/reportes', [
            'id_finca' => $this->finca->id_finca,
            'cantidad' => 5,
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Reporte registrado en historial.');
    }

    public function test_registrar_reporte_cantidad_invalida_falla(): void
    {
        $response = $this->withToken($this->token)->postJson('/api/ganadero/reportes', [
            'id_finca' => $this->finca->id_finca,
            'cantidad' => 0, // min:1
        ]);

        $response->assertStatus(422);
    }
}