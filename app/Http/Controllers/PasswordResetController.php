<?php

namespace App\Http\Controllers;

use App\Mail\CodigoVerificacionMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PasswordResetController extends Controller
{
    private const MINUTOS_VIGENCIA = 10;

    private function codigoHash(string $codigo): string
    {
        return hash('sha256', $codigo);
    }

    public function showForgotForm()
    {
        return view('auth.olvide_contrasena');
    }

    public function enviarCodigo(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email', '')));

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors(['email' => 'Ingrese un correo electrónico válido.']);
        }

        $usuario = DB::table('usuarios')
            ->where('email', $email)
            ->where('estado', 'A')
            ->first();

        if (!$usuario) {
            // Respuesta genérica para no revelar qué correos existen.
            return redirect()->route('password.verificar', ['email' => $email])
                ->with('info', 'Si el correo está registrado, recibirá un código de verificación.');
        }

        $ultimoVigente = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->value('created_at');

        if ($ultimoVigente && (float) now()->diffInSeconds($ultimoVigente) < 60) {
            return back()->withErrors(['email' => 'Espere un minuto antes de solicitar otro código.']);
        }

        $codigo = (string) random_int(100000, 999999);

        DB::table('password_reset_tokens')
            ->where('email', $email)
            ->delete();

        DB::table('password_reset_tokens')->insert([
            'email' => $email,
            'token' => $this->codigoHash($codigo),
            'created_at' => now(),
            'expires_at' => now()->addMinutes(self::MINUTOS_VIGENCIA),
        ]);

        Mail::to($email)->send(new CodigoVerificacionMail($codigo, (string) $usuario->nombre_completo));

        return redirect()->route('password.verificar', ['email' => $email])
            ->with('success', 'Enviamos un código de verificación a su correo. Revisá también la carpeta de spam.');
    }

    public function showVerificarForm(Request $request)
    {
        $email = strtolower(trim((string) $request->query('email', '')));

        return view('auth.verificar_codigo', ['email' => $email]);
    }

    public function restablecer(Request $request)
    {
        $email = strtolower(trim((string) $request->input('email', '')));
        $codigo = trim((string) $request->input('codigo', ''));
        $nueva = (string) $request->input('nueva', '');
        $repite = (string) $request->input('repite', '');

        if ($email === '' || $codigo === '' || $nueva === '') {
            return back()->withInput()->with('error', 'Debe completar todos los campos.');
        }
        if (strlen($nueva) < 6) {
            return back()->withInput()->with('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
        }
        if ($nueva !== $repite) {
            return back()->withInput()->with('error', 'Las contraseñas no coinciden.');
        }

        $token = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('expires_at', '>', now())
            ->orderByDesc('created_at')
            ->first();

        if (!$token || !hash_equals($this->codigoHash($codigo), (string) $token->token)) {
            return back()->withInput()->with('error', 'El código es incorrecto o está vencido.');
        }

        $id_usuario = DB::table('usuarios')
            ->where('email', $email)
            ->where('estado', 'A')
            ->value('id_usuario');

        if (!$id_usuario) {
            return back()->withInput()->with('error', 'No se encontró un usuario activo con ese correo.');
        }

        DB::table('usuarios')->where('id_usuario', (int) $id_usuario)->update([
            'contrasena' => bcrypt($nueva),
        ]);

        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return redirect()->route('login')->with('success', 'Contraseña restablecida correctamente. Ya podés iniciar sesión.');
    }
}