<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'venta_id', 'numero_factura', 'establecimiento', 'punto_expedicion',
    'timbrado', 'cliente_nombre', 'cliente_documento', 'cliente_direccion',
    'cliente_telefono', 'condicion_venta', 'total', 'total_exenta',
    'total_iva_5', 'total_iva_10', 'total_iva', 'estado',
])]
class Factura extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'facturas';

    protected $primaryKey = 'factura_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'numero_factura' => 'integer',
            'condicion_venta' => 'string',
            'total' => 'integer',
            'total_exenta' => 'integer',
            'total_iva_5' => 'integer',
            'total_iva_10' => 'integer',
            'total_iva' => 'integer',
            'estado' => 'string',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }

    public function detalle(): HasMany
    {
        return $this->hasMany(FacturaDetalle::class, 'factura_id', 'factura_id');
    }
}
