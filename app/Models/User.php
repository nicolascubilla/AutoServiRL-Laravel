<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['usuario', 'contrasena', 'nombre_completo', 'email', 'estado'])]
#[Hidden(['contrasena'])]
class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';

    protected $primaryKey = 'id_usuario';

    public $timestamps = false;

    public function getAuthPassword(): string
    {
        return $this->contrasena;
    }

    public function getAuthPasswordName(): string
    {
        return 'contrasena';
    }

    protected function casts(): array
    {
        return [
            'estado' => 'string',
        ];
    }

    public function getNombreAttribute(): string
    {
        return $this->nombre_completo;
    }
}
