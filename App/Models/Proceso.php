<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proceso extends Model
{
    protected $table = 'procesos';

    protected $fillable = [
        'codigo',
        'objeto',
        'descripcion',
        'estado',
        'etapa_actual_id',
        'area_actual_role',
        'created_by',
    ];
}
