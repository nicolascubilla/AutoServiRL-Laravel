<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LoginRehashTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_rehashes_contrasena_column(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        // Hash con coste 10 (formato del sistema original) para forzar el rehash al iniciar sesión.
        $hashOriginal = password_hash('clave_original', PASSWORD_BCRYPT, ['cost' => 10]);

        DB::table('usuarios')->insert([
            'usuario' => 'test_rehash',
            'contrasena' => $hashOriginal,
            'nombre_completo' => 'Rehash',
            'email' => 'rehash@test.com',
            'estado' => 'A',
        ]);
        $uid = (int) DB::table('usuarios')->where('usuario', 'test_rehash')->value('id_usuario');

        $ok = Auth::attempt(['usuario' => 'test_rehash', 'password' => 'clave_original']);
        $this->assertTrue($ok);
        $this->assertTrue(Auth::check());

        $nuevoHash = DB::table('usuarios')->where('id_usuario', $uid)->value('contrasena');
        $this->assertNotSame($hashOriginal, $nuevoHash);
        $this->assertTrue(password_verify('clave_original', $nuevoHash));

        Auth::logout();
    }
}