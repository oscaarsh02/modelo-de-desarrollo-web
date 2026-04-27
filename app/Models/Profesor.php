<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Profesor extends Model
{
    use HasFactory;

    protected $table = 'profesors';

    protected $fillable = [
        'nombre',
        'matricula',
        'user_id',
    ];

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function grupos()
    {
        return $this->hasMany(Grupo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}   
