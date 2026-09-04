<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['venta_id', 'pro_cod', 'cantidad', 'precio_unitario', 'subtotal'])]
class VentaDetalle extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'venta_detalle';

    protected $primaryKey = 'venta_detalle_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cantidad' => 'float',
            'precio_unitario' => 'integer',
            'subtotal' => 'integer',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'pro_cod', 'pro_cod');
    }
}
