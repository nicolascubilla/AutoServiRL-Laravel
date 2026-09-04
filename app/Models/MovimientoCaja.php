<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'caja_id', 'usuario_id', 'venta_id', 'tipo',
    'forma_pago', 'monto', 'descripcion',
])]
class MovimientoCaja extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'movimientos_caja';

    protected $primaryKey = 'movimiento_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'tipo' => 'string',
            'forma_pago' => 'string',
            'monto' => 'integer',
        ];
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id', 'caja_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_usuario');
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }
}
