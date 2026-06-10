<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoValidacion extends Model
{
    protected $table = 'resultados_validacion';

    protected $fillable = ['registro_verificacion_id', 'tipo', 'puntaje', 'resultado', 'detalles'];

    protected function casts(): array
    {
        return [
            'puntaje' => 'decimal:4',
            'detalles' => 'array',
        ];
    }

    public function registroVerificacion(): BelongsTo
    {
        return $this->belongsTo(RegistroVerificacion::class, 'registro_verificacion_id');
    }
}
