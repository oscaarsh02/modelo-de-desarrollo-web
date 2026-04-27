<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Salon extends Model
{
    protected $fillable = [
        'nombre'
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }
}
