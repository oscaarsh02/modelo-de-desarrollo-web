<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    protected $fillable = ['nombre', 'ponderacion', 'grupo_id'];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function actividades()
    {
        return $this->hasMany(Actividad::class);
    }
}
