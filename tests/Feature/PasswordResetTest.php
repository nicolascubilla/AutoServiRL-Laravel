<?php

namespace Tests\Feature;

use App\Mail\CodigoVerificacionMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use DatabaseTransactions;

    public function test_envia_codigo_y_restablece_contrasena(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasTable('password_reset_tokens')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $this->insertarUsuario('recupera_test', 'clave_original', 'Recupera Test', 'recupera@test.com');

        Mail::fake();

        $this->get(route('password.forgot'))->assertOk();

        $resp = $this->post(route('password.enviar'), ['email' => 'recupera@test.com']);
        $resp->assertRedirect(route('password.verificar', ['email' => 'recupera@test.com']));
        $resp->assertSessionHas('success');

        $codigo = null;
        Mail::assertSent(CodigoVerificacionMail::class, function (CodigoVerificacionMail $mail) use (&$codigo) {
            $codigo = $mail->codigo;
            return $mail->hasTo('recupera@test.com');
        });
        $this->assertNotNull($codigo);
        $this->assertMatchesRegularExpression('/^\d{6}$/', (string) $codigo);
        $this->assertSame(1, (int) DB::table('password_reset_tokens')->where('email', 'recupera@test.com')->count());

        $resp2 = $this->post(route('password.restablecer'), [
            'email' => 'recupera@test.com',
            'codigo' => (string) $codigo,
            'nueva' => 'nueva_clave_1',
            'repite' => 'nueva_clave_1',
        ]);

        $resp2->assertRedirect(route('login'));
        $resp2->assertSessionHas('success');

        $hash = DB::table('usuarios')->where('usuario', 'recupera_test')->value('contrasena');
        $this->assertTrue(password_verify('nueva_clave_1', $hash));
        $this->assertFalse(password_verify('clave_original', $hash));
        $this->assertSame(0, (int) DB::table('password_reset_tokens')->where('email', 'recupera@test.com')->count());
    }

    public function test_codigo_incorrecto_rechazado(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasTable('password_reset_tokens')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $this->insertarUsuario('recupera_test2', 'clave_original', 'Recupera Test 2', 'recupera2@test.com');

        DB::table('password_reset_tokens')->insert([
            'email' => 'recupera2@test.com',
            'token' => hash('sha256', '111111'),
            'created_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        $resp = $this->post(route('password.restablecer'), [
            'email' => 'recupera2@test.com',
            'codigo' => '222222',
            'nueva' => 'nueva_clave_2',
            'repite' => 'nueva_clave_2',
        ]);

        $resp->assertSessionHas('error');
        $this->assertTrue(password_verify(
            'clave_original',
            DB::table('usuarios')->where('usuario', 'recupera_test2')->value('contrasena')
        ));
    }

    public function test_codigo_vencido_rechazado(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasTable('password_reset_tokens')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $this->insertarUsuario('recupera_test3', 'clave_original', 'Recupera Test 3', 'recupera3@test.com');

        DB::table('password_reset_tokens')->insert([
            'email' => 'recupera3@test.com',
            'token' => hash('sha256', '333333'),
            'created_at' => now()->subMinutes(15),
            'expires_at' => now()->subMinutes(5),
        ]);

        $resp = $this->post(route('password.restablecer'), [
            'email' => 'recupera3@test.com',
            'codigo' => '333333',
            'nueva' => 'nueva_clave_3',
            'repite' => 'nueva_clave_3',
        ]);

        $resp->assertSessionHas('error');
        $this->assertTrue(password_verify(
            'clave_original',
            DB::table('usuarios')->where('usuario', 'recupera_test3')->value('contrasena')
        ));
    }

    public function test_reenvio_limitado_a_un_minuto(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasTable('password_reset_tokens')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $this->insertarUsuario('recupera_test4', 'clave_original', 'Recupera Test 4', 'recupera4@test.com');

        Mail::fake();

        $this->post(route('password.enviar'), ['email' => 'recupera4@test.com']);
        $resp = $this->post(route('password.enviar'), ['email' => 'recupera4@test.com']);

        $resp->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Espere un minuto',
            session('errors')->first('email')
        );
        $this->assertSame(1, (int) DB::table('password_reset_tokens')->where('email', 'recupera4@test.com')->count());
    }

    public function test_no_se_crea_usuario_sin_email(): void
    {
        if (! Schema::hasTable('usuarios')) {
            $this->markTestSkipped('Requiere PostgreSQL (AutoserviRL). Ejecutar con la config phpunit.pgsql.xml.');
        }

        $adminId = $this->insertarUsuario(
            'admin_prueba_reset_' . random_int(10000, 99999),
            'clave_admin',
            'Admin Reset',
            'adminreset' . random_int(1000, 9999) . '@test.com'
        );
        $this->actingAs(\App\Models\User::query()->find($adminId));

        $usuarioSinEmail = 'sin_email_' . random_int(10000, 99999);

        $resp = $this->post(route('usuarios.guardar'), [
            'usuario' => $usuarioSinEmail,
            'nombre_completo' => 'Sin Email',
            'contrasena' => 'clave123',
        ]);
        $resp->assertRedirect(route('usuarios'));
        $resp->assertSessionHas('error');
        $this->assertSame(0, (int) DB::table('usuarios')->where('usuario', $usuarioSinEmail)->count());
    }

    private function insertarUsuario(string $usuario, string $contrasena, string $nombre, string $email): int
    {
        return (int) DB::table('usuarios')->insertGetId([
            'usuario' => $usuario,
            'contrasena' => bcrypt($contrasena),
            'nombre_completo' => $nombre,
            'email' => $email,
            'estado' => 'A',
        ], 'id_usuario');
    }
}