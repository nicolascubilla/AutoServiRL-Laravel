<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pro_cod', 'cantidad', 'stock_minimo'])]
class Stock extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'stock';

    protected $primaryKey = 'stock_id';

    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'cantidad' => 'float',
            'stock_minimo' => 'float',
        ];
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'pro_cod', 'pro_cod');
    }
}
