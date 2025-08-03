<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cita extends Model
{
    use HasFactory;
    protected $fillable = [
        'cliente_id', 'barberia_id', 'barbero_id', 'servicio_id', 'fecha', 'hora',
        'estado', 'metodo_pago', 'estado_pago', 'codigo_transaccion', 'comprobante_url', 'total',
    ];

    public function cliente() {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function barberia() {
        return $this->belongsTo(Barberia::class);
    }

    public function barbero() {
        return $this->belongsTo(Barbero::class);
    }

    //
    
    public function servicio() {
        return $this->belongsTo(Servicio::class);
    }

    public function calificacion() {
        return $this->hasOne(Calificacion::class);
    }
}