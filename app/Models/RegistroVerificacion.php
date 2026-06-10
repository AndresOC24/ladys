<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RegistroVerificacion extends Model
{
    protected $table = 'registros_verificacion';

    protected $fillable = ['usuaria_id', 'fecha_inicio', 'fecha_resolucion', 'estado'];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'datetime',
            'fecha_resolucion' => 'datetime',
        ];
    }

    public function usuaria(): BelongsTo
    {
        return $this->belongsTo(Usuaria::class, 'usuaria_id');
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'registro_verificacion_id');
    }

    public function datosDocumento(): HasOne
    {
        return $this->hasOne(DatosDocumento::class, 'registro_verificacion_id');
    }

    public function resultadosValidacion(): HasMany
    {
        return $this->hasMany(ResultadoValidacion::class, 'registro_verificacion_id');
    }

    public function revisionAdministrativa(): HasOne
    {
        return $this->hasOne(RevisionAdministrativa::class, 'registro_verificacion_id');
    }
}
