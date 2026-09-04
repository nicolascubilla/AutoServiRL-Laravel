<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['venta_id', 'forma_pago', 'monto'])]
class VentaPago extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'venta_pagos';

    protected $primaryKey = 'venta_pago_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'forma_pago' => 'string',
            'monto' => 'integer',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id', 'venta_id');
    }
}
