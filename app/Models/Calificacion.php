<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Calificacion extends Model
{
    protected $table = 'calificaciones';

    protected $fillable = ['actividad_id', 'alumno_id', 'calificacion'];

    public function actividad()
    {
        return $this->belongsTo(Actividad::class);
    }

    public function alumno()
    {
        return $this->belongsTo(Alumno::class);
    }
}
