<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = ['nombre', 'descripcion'];

    public function usuarias(): HasMany
    {
        return $this->hasMany(Usuaria::class, 'rol_id');
    }
}
