<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'factura_id', 'pro_cod', 'descripcion', 'cantidad',
    'precio_unitario', 'exenta', 'gravada_5', 'gravada_10', 'subtotal',
])]
class FacturaDetalle extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'factura_detalle';

    protected $primaryKey = 'factura_detalle_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cantidad' => 'float',
            'precio_unitario' => 'integer',
            'exenta' => 'integer',
            'gravada_5' => 'integer',
            'gravada_10' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function factura(): BelongsTo
    {
        return $this->belongsTo(Factura::class, 'factura_id', 'factura_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'pro_cod', 'pro_cod');
    }
}
