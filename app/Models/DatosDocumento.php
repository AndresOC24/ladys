<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DatosDocumento extends Model
{
    protected $table = 'datos_documento';

    protected $fillable = [
        'registro_verificacion_id',
        'numero_cedula',
        'nombre_completo',
        'fecha_nacimiento',
        'fecha_emision',
        'fecha_vencimiento',
        'lugar_nacimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function registroVerificacion(): BelongsTo
    {
        return $this->belongsTo(RegistroVerificacion::class, 'registro_verificacion_id');
    }
}
