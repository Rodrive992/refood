<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartaCategoria extends Model
{
    protected $table = 'carta_categorias';

    protected $fillable = [
        'id_local',
        'nombre',
        'orden',
        'activo',
    ];

    public function items()
    {
        return $this->hasMany(CartaItem::class, 'id_categoria');
    }
}
