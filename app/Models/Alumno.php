<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alumno extends Model
{
    protected $fillable = [
        'nombre',
        'matricula',
        'correo'
    ];

    public function grupos()
    {
        return $this->belongsToMany(Grupo::class, 'alumno_grupos')
            ->withPivot('baja_at')
            ->withTimestamps();
    }

    public function gruposActivos()
    {
        return $this->belongsToMany(Grupo::class, 'alumno_grupos')
            ->withPivot('baja_at')
            ->withTimestamps()
            ->wherePivotNull('baja_at');
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }
}
