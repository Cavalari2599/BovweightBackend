<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AuthTest extends TestCase
{
    use RefreshDatabase;

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

    private function crearUsuario(array $attrs = []): User
    {
        return User::create(array_merge([
            'identificacion_usuario' => 100001,
            'correo'                 => 'ganadero@test.com',
            'clave'                  => Hash::make('password123'),
            'id_rol'                 => 1,
            'estado'                 => true,
            'nombre_usuario'         => 'Carlos',
            'apellido1_usuario'      => 'Rodríguez',
            'apellido2_usuario'      => 'Mora',
        ], $attrs));
    }

    // ─── LOGIN ────────────────────────────────────────────────────────────────

    public function test_login_exitoso(): void
    {
        $this->crearUsuario();

        $response = $this->postJson('/api/login', [
            'correo' => 'ganadero@test.com',
            'clave'  => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['token', 'usuario'])
                 ->assertJsonPath('usuario.correo', 'ganadero@test.com');
    }

    public function test_login_credenciales_incorrectas(): void
    {
        $this->crearUsuario();

        $response = $this->postJson('/api/login', [
            'correo' => 'ganadero@test.com',
            'clave'  => 'claveEquivocada',
        ]);

        $response->assertStatus(401)
                 ->assertJsonPath('message', 'Credenciales incorrectas.');
    }

    public function test_login_usuario_inexistente(): void
    {
        $response = $this->postJson('/api/login', [
            'correo' => 'noexiste@test.com',
            'clave'  => 'password123',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_usuario_inactivo(): void
    {
        $this->crearUsuario(['estado' => false]);

        $response = $this->postJson('/api/login', [
            'correo' => 'ganadero@test.com',
            'clave'  => 'password123',
        ]);

        $response->assertStatus(403)
                 ->assertJsonPath('message', 'Usuario inactivo.');
    }

    public function test_login_sin_correo_falla_validacion(): void
    {
        $response = $this->postJson('/api/login', ['clave' => 'password123']);
        $response->assertStatus(422);
    }

    // ─── LOGOUT ───────────────────────────────────────────────────────────────

    public function test_logout_exitoso(): void
    {
        $user = $this->crearUsuario();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertStatus(200)
                 ->assertJsonPath('message', 'Sesión cerrada correctamente.');
    }

    public function test_logout_sin_autenticar_retorna_401(): void
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(401);
    }

    // ─── ME ───────────────────────────────────────────────────────────────────

    public function test_me_retorna_datos_usuario_autenticado(): void
    {
        $user  = $this->crearUsuario();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/me');

        $response->assertStatus(200)
                 ->assertJsonPath('correo', 'ganadero@test.com')
                 ->assertJsonPath('identificacion', 100001);
    }

    public function test_me_sin_autenticar_retorna_401(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }

    // ─── FORGOT PASSWORD ──────────────────────────────────────────────────────

    public function test_forgot_password_correo_valido(): void
    {
        Mail::fake();
        $this->crearUsuario();

        $response = $this->postJson('/api/auth/forgot-password', [
            'correo' => 'ganadero@test.com',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('mensaje', 'Se ha enviado un código de verificación al correo proporcionado');

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'ganadero@test.com']);
    }

    public function test_forgot_password_correo_inexistente(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', [
            'correo' => 'noexiste@test.com',
        ]);

        $response->assertStatus(422)
                 ->assertJsonPath('mensaje', 'El correo no existe en el sistema');
    }

    public function test_forgot_password_sin_correo_falla(): void
    {
        $response = $this->postJson('/api/auth/forgot-password', []);
        $response->assertStatus(422);
    }

    // ─── RESET PASSWORD ───────────────────────────────────────────────────────

    public function test_reset_password_exitoso(): void
    {
        $user   = $this->crearUsuario();
        $codigo = '123456';

        DB::table('password_reset_tokens')->insert([
            'email'      => 'ganadero@test.com',
            'token'      => Hash::make($codigo),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'correo'              => 'ganadero@test.com',
            'codigo'              => $codigo,
            'clave'               => 'NuevaClave123',
            'clave_confirmation'  => 'NuevaClave123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('mensaje', 'Contraseña restablecida exitosamente');

        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'ganadero@test.com']);
    }

    public function test_reset_password_codigo_invalido(): void
    {
        $this->crearUsuario();

        DB::table('password_reset_tokens')->insert([
            'email'      => 'ganadero@test.com',
            'token'      => Hash::make('123456'),
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/auth/reset-password', [
            'correo'             => 'ganadero@test.com',
            'codigo'             => '999999',
            'clave'              => 'NuevaClave123',
            'clave_confirmation' => 'NuevaClave123',
        ]);

        $response->assertStatus(422);
    }

    public function test_reset_password_clave_sin_confirmacion_falla(): void
    {
        $response = $this->postJson('/api/auth/reset-password', [
            'correo' => 'ganadero@test.com',
            'codigo' => '123456',
            'clave'  => 'NuevaClave123',
        ]);

        $response->assertStatus(422);
    }
}