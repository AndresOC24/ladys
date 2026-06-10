<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = ['registro_verificacion_id', 'tipo', 'ruta_archivo', 'hash_archivo'];

    public function registroVerificacion(): BelongsTo
    {
        return $this->belongsTo(RegistroVerificacion::class, 'registro_verificacion_id');
    }
}
