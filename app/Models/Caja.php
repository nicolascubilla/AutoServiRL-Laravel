<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'usuario_apertura_id', 'monto_inicial', 'fecha_cierre',
    'usuario_cierre_id', 'monto_cierre', 'estado', 'observacion',
    'monto_esperado', 'diferencia',
])]
class Caja extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'cajas';

    protected $primaryKey = 'caja_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'monto_inicial' => 'integer',
            'monto_cierre' => 'integer',
            'monto_esperado' => 'integer',
            'diferencia' => 'integer',
            'estado' => 'string',
        ];
    }

    public function usuarioApertura(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_apertura_id', 'id_usuario');
    }

    public function usuarioCierre(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_cierre_id', 'id_usuario');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'caja_id', 'caja_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'caja_id', 'caja_id');
    }
}
