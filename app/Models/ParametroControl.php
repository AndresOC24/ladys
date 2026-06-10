<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParametroControl extends Model
{
    protected $table = 'parametros_control';

    protected $fillable = ['categoria', 'clave', 'valor', 'descripcion', 'activo'];

    protected function casts(): array
    {
        return [
            'activo' => 'boolean',
        ];
    }

    /**
     * Get the value of an active control parameter by its key.
     */
    public static function valorDe(string $clave, ?string $default = null): ?string
    {
        $parametro = static::where('clave', $clave)->where('activo', true)->first();

        return $parametro?->valor ?? $default;
    }
}
