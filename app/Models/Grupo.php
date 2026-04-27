<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = [
        'nombre',
        'materia_id',
        'profesor_id',
    ];

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function alumnos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_grupos')
            ->withPivot('baja_at')
            ->withTimestamps();
    }

    public function alumnosActivos()
    {
        return $this->belongsToMany(Alumno::class, 'alumno_grupos')
            ->withPivot('baja_at')
            ->withTimestamps()
            ->wherePivotNull('baja_at');
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function categorias()
    {
        return $this->hasMany(Categoria::class);
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }
}
