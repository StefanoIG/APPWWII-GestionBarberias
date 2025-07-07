<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Barbero extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'barberia_id', 'foto_url', 'biografia', 'estado'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function barberia() {
        return $this->belongsTo(Barberia::class);
    }

    public function servicios() {
        return $this->belongsToMany(Servicio::class, 'barbero_servicio');
    }

    public function horarios() {
        return $this->hasMany(HorarioBarbero::class);
    }
    
    public function citas() {
        return $this->hasMany(Cita::class);
    }
    
    public function calificaciones() {
        return $this->hasMany(Calificacion::class);
    }
}