<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $fillable = [
        'clave',
        'nombre'
    ];

    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
}
