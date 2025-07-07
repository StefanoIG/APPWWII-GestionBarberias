<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Barberia extends Model
{
    use HasFactory;
    protected $fillable = ['nombre', 'descripcion', 'direccion', 'telefono', 'email', 'logo_url', 'estado', 'owner_id'];
    //1 - 1
    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }
    // 1 - n
    public function barberos() {
        return $this->hasMany(Barbero::class);
    }
    
    public function servicios() {
        return $this->hasMany(Servicio::class);
    }
    
    public function citas() {
        return $this->hasMany(Cita::class);
    }
}