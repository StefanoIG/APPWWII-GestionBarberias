<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Servicio extends Model
{
    use HasFactory;
    protected $fillable = ['barberia_id', 'nombre', 'descripcion', 'duracion', 'precio'];

    public function barberia() {
        return $this->belongsTo(Barberia::class);
    }

    public function barberos() {
        return $this->belongsToMany(Barbero::class, 'barbero_servicio');
    }
}