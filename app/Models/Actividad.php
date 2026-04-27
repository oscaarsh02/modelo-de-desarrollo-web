<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Actividad extends Model
{
    protected $table = 'actividades';

    protected $fillable = ['nombre', 'grupo_id', 'categoria_id'];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function calificaciones()
    {
        return $this->hasMany(Calificacion::class);
    }
}
