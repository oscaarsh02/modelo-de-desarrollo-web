<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $fillable = [
        'nrc',
        'grupo_id',
        'profesor_id',
        'salon_id',
        'dia',
        'hora'
    ];

    public function grupo()
    {
        return $this->belongsTo(Grupo::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function salon()
    {
        return $this->belongsTo(Salon::class);
    }
}
