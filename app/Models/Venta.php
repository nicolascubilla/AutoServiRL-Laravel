<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['caja_id', 'usuario_id', 'total', 'estado', 'observacion'])]
class Venta extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'ventas';

    protected $primaryKey = 'venta_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'total' => 'integer',
            'estado' => 'string',
        ];
    }

    public function detalle(): HasMany
    {
        return $this->hasMany(VentaDetalle::class, 'venta_id', 'venta_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(VentaPago::class, 'venta_id', 'venta_id');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id', 'caja_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_usuario');
    }

    public function factura(): HasOne
    {
        return $this->hasOne(Factura::class, 'venta_id', 'venta_id');
    }
}
