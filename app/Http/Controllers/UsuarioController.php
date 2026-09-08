<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UsuarioController extends Controller
{
    public function cambiarContrasenaForm()
    {
        return view('cambiar_contrasena');
    }

    public function cambiarContrasena(Request $request)
    {
        $usuario_id = (int) session('user_id');

        $actual = (string) $request->input('actual', '');
        $nueva = (string) $request->input('nueva', '');
        $repite = (string) $request->input('repite', '');

        if ($actual === '' || $nueva === '') {
            return redirect()->route('cambiar_contrasena')
                ->with('error', 'Debe completar todos los campos.');
        }
        if ($nueva !== $repite) {
            return redirect()->route('cambiar_contrasena')
                ->with('error', 'La nueva contraseña y su confirmación no coinciden.');
        }
        if (strlen($nueva) < 6) {
            return redirect()->route('cambiar_contrasena')
                ->with('error', 'La nueva contraseña debe tener al menos 6 caracteres.');
        }

        try {
            $hash = DB::table('usuarios')
                ->where('id_usuario', $usuario_id)
                ->where('estado', 'A')
                ->value('contrasena');

            if (!$hash || !password_verify($actual, $hash)) {
                return redirect()->route('cambiar_contrasena')
                    ->with('error', 'La contraseña actual es incorrecta.');
            }

            DB::table('usuarios')
                ->where('id_usuario', $usuario_id)
                ->update(['contrasena' => bcrypt($nueva)]);

            return redirect()->route('cambiar_contrasena')
                ->with('success', 'Contraseña actualizada correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('cambiar_contrasena')
                ->with('error', 'No fue posible cambiar la contraseña.');
        }
    }

    public function listar()
    {
        $usuarios = DB::table('usuarios')
            ->orderBy('id_usuario')
            ->get();

        return view('usuarios', ['usuarios' => $usuarios]);
    }

    public function crear(Request $request)
    {
        $usuario = trim((string) $request->input('usuario', ''));
        $nombre = trim((string) $request->input('nombre_completo', ''));
        $email = strtolower(trim((string) $request->input('email', '')));
        $contrasena = (string) $request->input('contrasena', '');

        if ($usuario === '' || $nombre === '' || $contrasena === '') {
            return redirect()->route('usuarios')
                ->with('error', 'Debe completar el usuario, el nombre y la contraseña.');
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->route('usuarios')
                ->with('error', 'Debe ingresar un correo electrónico válido (es obligatorio para la recuperación de contraseña).');
        }
        if (strlen($contrasena) < 6) {
            return redirect()->route('usuarios')
                ->with('error', 'La contraseña debe tener al menos 6 caracteres.');
        }
        if (DB::table('usuarios')->where('usuario', $usuario)->exists()) {
            return redirect()->route('usuarios')
                ->with('error', 'El nombre de usuario ya está en uso.');
        }

        try {
            DB::table('usuarios')->insert([
                'usuario' => $usuario,
                'nombre_completo' => $nombre,
                'email' => $email,
                'contrasena' => bcrypt($contrasena),
                'estado' => 'A',
            ]);

            return redirect()->route('usuarios')
                ->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('usuarios')
                ->with('error', 'No fue posible crear el usuario.');
        }
    }

    public function cambiarEstado(Request $request)
    {
        $id = (int) $request->input('id_usuario', 0);
        $usuario = $id > 0 ? DB::table('usuarios')->where('id_usuario', $id)->first() : null;

        if (!$usuario) {
            return redirect()->route('usuarios')
                ->with('error', 'El usuario no existe.');
        }
        $idActual = Auth::check() ? (int) Auth::id() : (int) session('user_id');
        if ((int) $usuario->id_usuario === $idActual) {
            return redirect()->route('usuarios')
                ->with('error', 'No puede inactivar su propia cuenta.');
        }

        $nuevoEstado = $usuario->estado === 'A' ? 'I' : 'A';
        DB::table('usuarios')->where('id_usuario', $id)->update(['estado' => $nuevoEstado]);

        $accion = $nuevoEstado === 'A' ? 'activado' : 'inactivado';

        return redirect()->route('usuarios')
            ->with('success', "El usuario fue {$accion} correctamente.");
    }

    public function editar(Request $request)
    {
        $id = (int) $request->input('id_usuario', 0);
        $nombre = trim((string) $request->input('nombre_completo', ''));

        if ($id <= 0 || $nombre === '') {
            return redirect()->route('usuarios')
                ->with('error', 'Debe indicar el usuario y el nombre completo.');
        }
        if (strlen($nombre) > 100) {
            return redirect()->route('usuarios')
                ->with('error', 'El nombre completo no puede superar los 100 caracteres.');
        }
        if (!DB::table('usuarios')->where('id_usuario', $id)->exists()) {
            return redirect()->route('usuarios')
                ->with('error', 'El usuario no existe.');
        }

        try {
            DB::table('usuarios')->where('id_usuario', $id)->update([
                'nombre_completo' => $nombre,
            ]);

            return redirect()->route('usuarios')
                ->with('success', 'Usuario actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->route('usuarios')
                ->with('error', 'No fue posible actualizar el usuario.');
        }
    }
}
