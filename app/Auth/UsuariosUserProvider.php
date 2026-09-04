<?php

namespace App\Auth;

use Illuminate\Auth\EloquentUserProvider;

/**
 * Proveedor de autenticación que replica la lógica del login original:
 * sólo permite iniciar sesión a usuarios con estado = 'A' (activo).
 * La contraseña se verifica con bcrypt contra usuarios.contrasena
 * (getAuthPassword del modelo User) usando el mecanismo estándar de Laravel.
 */
class UsuariosUserProvider extends EloquentUserProvider
{
    protected function newModelQuery($model = null)
    {
        $query = parent::newModelQuery($model);

        return $query->where('estado', '=', 'A');
    }
}
