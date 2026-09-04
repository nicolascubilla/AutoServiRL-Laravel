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
}
