<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionAdministrativa extends Model
{
    protected $table = 'revisiones_administrativas';

    protected $fillable = [
        'registro_verificacion_id',
        'administrador_id',
        'decision',
        'observaciones',
        'fecha_revision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_revision' => 'datetime',
        ];
    }

    public function registroVerificacion(): BelongsTo
    {
        return $this->belongsTo(RegistroVerificacion::class, 'registro_verificacion_id');
    }

    public function administrador(): BelongsTo
    {
        return $this->belongsTo(Usuaria::class, 'administrador_id');
    }
}
