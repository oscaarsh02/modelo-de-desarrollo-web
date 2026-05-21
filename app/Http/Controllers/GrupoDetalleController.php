<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Profesor;
use App\Services\CalificacionesService;

class GrupoDetalleController extends Controller
{
    public function show(Grupo $grupo, CalificacionesService $calificaciones)
    {
        $this->authorizeProfesor($grupo);

        $grupo->load([
            'materia',
            'horarios.salon',
            'categorias.actividades.calificaciones',
        ]);

        $alumnos       = $grupo->alumnosActivos()->orderBy('nombre')->get();
        $alumnosBaja   = $grupo->alumnos()->wherePivotNotNull('baja_at')->orderBy('nombre')->get();
        $categorias    = $grupo->categorias;

        $concentrado = $calificaciones->construirConcentrado($grupo, $alumnos, $categorias);

        return view('profesores.grupo_show', compact('grupo', 'alumnos', 'alumnosBaja', 'categorias', 'concentrado'));
    }

    private function authorizeProfesor(Grupo $grupo): void
    {
        $user = auth()->user();

        if ($user->role !== 'profesor') {
            abort(403);
        }

        $profesor = Profesor::where('user_id', $user->id)
            ->orWhere('matricula', $user->matricula)
            ->first();

        if (! $profesor || $grupo->profesor_id !== $profesor->id) {
            abort(403);
        }
    }
}
