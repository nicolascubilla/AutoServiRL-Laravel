<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UsuarioCreacionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_se_puede_crear_usuario_y_acceder_con_el(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = $this->crearUsuario('admin_prueba', 'clave_initial', 'Admin Prueba');
        $this->actingAs($admin);

        $resp = $this->post(route('usuarios.guardar'), [
            'usuario' => 'operador_nuevo',
            'nombre_completo' => 'Operador Nuevo',
            'email' => 'operador@test.com',
            'contrasena' => 'clave123',
        ]);

        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('success');

        $fila = DB::table('usuarios')->where('usuario', 'operador_nuevo')->first();
        $this->assertNotNull($fila);
        $this->assertSame('A', $fila->estado);
        $this->assertSame('Operador Nuevo', $fila->nombre_completo);
        $this->assertSame('operador@test.com', $fila->email);
        $this->assertTrue(password_verify('clave123', $fila->contrasena));
        $this->assertFalse(password_verify('otra_clave', $fila->contrasena));

        Auth::logout();
        $this->assertTrue(Auth::attempt(['usuario' => 'operador_nuevo', 'password' => 'clave123']));
        Auth::logout();
    }

    public function test_no_se_crea_usuario_con_contraseña_corta(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = $this->crearUsuario('admin_prueba', 'clave_initial', 'Admin Prueba');
        $this->actingAs($admin);

        $resp = $this->post(route('usuarios.guardar'), [
            'usuario' => 'corto',
            'nombre_completo' => 'Corto',
            'contrasena' => '123',
        ]);

        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('error');
        $this->assertSame(0, (int) DB::table('usuarios')->where('usuario', 'corto')->count());
    }

    public function test_no_se_crea_usuario_con_nombre_repetido(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $admin = $this->crearUsuario('admin_prueba', 'clave_initial', 'Admin Prueba');
        $this->actingAs($admin);

        DB::table('usuarios')->insert([
            'usuario' => 'existente',
            'contrasena' => bcrypt('clave123'),
            'nombre_completo' => 'Existente',
            'estado' => 'A',
        ]);

        $resp = $this->post(route('usuarios.guardar'), [
            'usuario' => 'existente',
            'nombre_completo' => 'Otro',
            'contrasena' => 'clave123',
        ]);

        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('error');
        $this->assertSame(1, (int) DB::table('usuarios')->where('usuario', 'existente')->count());
    }

    private function crearUsuario(string $usuario, string $contrasena, string $nombre): User
    {
        $id = (int) DB::table('usuarios')->insertGetId([
            'usuario' => $usuario,
            'contrasena' => bcrypt($contrasena),
            'nombre_completo' => $nombre,
            'estado' => 'A',
        ], 'id_usuario');

        return User::query()->find($id);
    }
}