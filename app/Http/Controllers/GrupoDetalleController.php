<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Profesor;

class GrupoDetalleController extends Controller
{
    public function show(Grupo $grupo)
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

        $concentrado = $alumnos->map(function ($alumno) use ($categorias) {
            $filaCats = $categorias->map(function ($cat) use ($alumno) {
                $actividades = $cat->actividades->map(function ($act) use ($alumno) {
                    $cal = $act->calificaciones->firstWhere('alumno_id', $alumno->id);
                    return ['actividad' => $act, 'calificacion' => $cal?->calificacion];
                });

                $valores  = $actividades->whereNotNull('calificacion')->pluck('calificacion');
                $promedio = $valores->isNotEmpty() ? round($valores->average(), 2) : null;

                return ['categoria' => $cat, 'actividades' => $actividades, 'promedio' => $promedio];
            });

            $ponderadoTotal    = 0;
            $ponderacionAcum   = 0;
            $filaCats->each(function ($f) use (&$ponderadoTotal, &$ponderacionAcum) {
                if ($f['promedio'] !== null) {
                    $ponderadoTotal  += $f['promedio'] * ($f['categoria']->ponderacion / 100);
                    $ponderacionAcum += $f['categoria']->ponderacion;
                }
            });

            return [
                'alumno'            => $alumno,
                'categorias'        => $filaCats,
                'promedio_ponderado' => $ponderacionAcum > 0 ? round($ponderadoTotal, 2) : null,
            ];
        });

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
