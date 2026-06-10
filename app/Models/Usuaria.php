<?php

namespace App\Models;

use Database\Factories\UsuariaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuaria extends Authenticatable
{
    /** @use HasFactory<UsuariaFactory> */
    use HasFactory, Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol_id',
        'telefono',
        'fecha_nacimiento',
        'estado_verificacion',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'fecha_nacimiento' => 'date',
            'password' => 'hashed',
        ];
    }

    protected static function newFactory(): UsuariaFactory
    {
        return UsuariaFactory::new();
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    public function registrosVerificacion(): HasMany
    {
        return $this->hasMany(RegistroVerificacion::class, 'usuaria_id');
    }

    public function revisionesRealizadas(): HasMany
    {
        return $this->hasMany(RevisionAdministrativa::class, 'administrador_id');
    }
}
