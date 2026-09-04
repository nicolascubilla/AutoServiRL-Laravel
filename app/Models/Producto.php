<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['codigo_barra', 'descripcion', 'precio', 'activo', 'codigo', 'tasa_iva'])]
#[Hidden([])]
class Producto extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'productos';

    protected $primaryKey = 'pro_cod';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'precio' => 'integer',
            'activo' => 'string',
            'tasa_iva' => 'string',
        ];
    }

    public function stock(): HasOne
    {
        return $this->hasOne(Stock::class, 'pro_cod', 'pro_cod');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(StockMovimiento::class, 'pro_cod', 'pro_cod');
    }
}
