<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsuarioEstadoEdicionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_inactiva_usuario_y_no_puede_iniciar_sesion(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = User::query()->find($this->crearUsuario('admin_estado_' . random_int(100000, 999999), 'clave_admin', 'Admin Estado'));

        $usuarioObjetivo = 'objetivo_estado_' . random_int(100000, 999999);
        $idObjetivo = $this->crearUsuario($usuarioObjetivo, 'clave_objetivo', 'Objetivo');

        $this->actingAs($admin);

        $resp = $this->post(route('usuarios.estado'), ['id_usuario' => $idObjetivo]);
        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('success');
        $this->assertSame('I', DB::table('usuarios')->where('id_usuario', $idObjetivo)->value('estado'));

        Auth::logout();
        $this->assertFalse(Auth::attempt(['usuario' => $usuarioObjetivo, 'password' => 'clave_objetivo']));
        $this->assertFalse(Auth::attempt(['usuario' => $usuarioObjetivo, 'password' => 'clave_objetivo', 'estado' => 'A']));

        $this->actingAs($admin);
        $this->post(route('usuarios.estado'), ['id_usuario' => $idObjetivo]);
        $this->assertSame('A', DB::table('usuarios')->where('id_usuario', $idObjetivo)->value('estado'));

        Auth::logout();
        $this->assertTrue(Auth::attempt(['usuario' => $usuarioObjetivo, 'password' => 'clave_objetivo']));
    }

    public function test_no_puede_inactivarse_a_si_mismo(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = User::query()->find($this->crearUsuario('admin_self_' . random_int(100000, 999999), 'clave_admin', 'Admin Self'));
        $this->actingAs($admin);

        $resp = $this->post(route('usuarios.estado'), ['id_usuario' => $admin->id_usuario]);
        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('error');
        $this->assertSame('A', (string) DB::table('usuarios')->where('id_usuario', $admin->id_usuario)->value('estado'));
    }

    public function test_edita_nombre_completo(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = User::query()->find($this->crearUsuario('admin_edit_' . random_int(100000, 999999), 'clave_admin', 'Admin Edit'));
        $idObjetivo = $this->crearUsuario('objetivo_edit_' . random_int(100000, 999999), 'clave_obj', 'Nombre Viejo');

        $this->actingAs($admin);

        $resp = $this->post(route('usuarios.editar'), [
            'id_usuario' => $idObjetivo,
            'nombre_completo' => 'Nombre Nuevo',
        ]);
        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('success');
        $this->assertSame('Nombre Nuevo', DB::table('usuarios')->where('id_usuario', $idObjetivo)->value('nombre_completo'));
    }

    public function test_editar_sin_nombre_rechazado(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = User::query()->find($this->crearUsuario('admin_edit2_' . random_int(100000, 999999), 'clave_admin', 'Admin Edit 2'));
        $idObjetivo = $this->crearUsuario('objetivo_edit2_' . random_int(100000, 999999), 'clave_obj', 'Nombre Fijo');

        $this->actingAs($admin);

        $resp = $this->post(route('usuarios.editar'), [
            'id_usuario' => $idObjetivo,
            'nombre_completo' => '',
        ]);
        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('error');
        $this->assertSame('Nombre Fijo', DB::table('usuarios')->where('id_usuario', $idObjetivo)->value('nombre_completo'));
    }

    private function crearUsuario(string $usuario, string $contrasena, string $nombre): int
    {
        return (int) DB::table('usuarios')->insertGetId([
            'usuario' => $usuario,
            'contrasena' => bcrypt($contrasena),
            'nombre_completo' => $nombre,
            'email' => strtolower($usuario) . '@test.com',
            'estado' => 'A',
        ], 'id_usuario');
    }
}