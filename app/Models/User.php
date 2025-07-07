<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nombre', 'email', 'password', 'telefono', 'cedula', 'role_id', 'bloqueado',
    ];

    public function role() {
        return $this->belongsTo(Role::class);
    }

    // Si el usuario es un dueño
    public function barberia() {
        return $this->hasOne(Barberia::class, 'owner_id');
    }

    // Si el usuario es un barbero
    public function barbero() {
        return $this->hasOne(Barbero::class, 'user_id');
    }

    // Si el usuario es un cliente
    public function citas() {
        return $this->hasMany(Cita::class, 'cliente_id');
    }
}