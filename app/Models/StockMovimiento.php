<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pro_cod', 'tipo', 'cantidad', 'stock_resultante', 'observacion', 'usuario_id'])]
class StockMovimiento extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'stock_movimientos';

    protected $primaryKey = 'mov_id';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cantidad' => 'float',
            'stock_resultante' => 'float',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'pro_cod', 'pro_cod');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id_usuario');
    }
}
