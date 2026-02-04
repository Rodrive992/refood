<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartaItem extends Model
{
    protected $table = 'carta_items';

    protected $fillable = [
        'id_local',
        'id_categoria',
        'nombre',
        'descripcion',
        'precio',
        'costo',
        'activo',
        'orden',
    ];

    public function categoria()
    {
        return $this->belongsTo(CartaCategoria::class, 'id_categoria');
    }
}
