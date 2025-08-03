<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = [
        'cita_id',
        'cliente_id',
        'barbero_id',
        'puntuacion',
        'comentario',
    ];
}
