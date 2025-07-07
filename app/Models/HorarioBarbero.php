<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioBarbero extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'horarios_barbero';

    /**
     * Indicates if the model should be timestamped.
     * La migración no incluye timestamps, por lo que lo desactivamos.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'barbero_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    /**
     * Get the barbero that owns the horario.
     */
    public function barbero(): BelongsTo
    {
        return $this->belongsTo(Barbero::class);
    }
}
