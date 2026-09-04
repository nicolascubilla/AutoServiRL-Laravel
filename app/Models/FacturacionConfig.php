<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'nombre_negocio', 'actividad', 'propietario', 'ruc', 'direccion',
    'telefono', 'ciudad', 'timbrado', 'fecha_inicio_vigencia',
    'fecha_fin_vigencia', 'establecimiento', 'punto_expedicion',
    'ultimo_numero_factura',
])]
class FacturacionConfig extends Model
{
    protected $table = 'facturacion_config';

    protected $primaryKey = 'config_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'fecha_inicio_vigencia' => 'date',
            'fecha_fin_vigencia' => 'date',
            'ultimo_numero_factura' => 'integer',
        ];
    }
}
