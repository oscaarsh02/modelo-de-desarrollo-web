<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\Profesor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CalificacionController extends Controller
{
    public function form(Actividad $actividad)
    {
        $grupo = $actividad->load('categoria', 'grupo.materia')->grupo;
        $this->authorizeProfesor($grupo);

        $alumnos = $grupo->alumnosActivos()->orderBy('nombre')->get();

        $calificaciones = Calificacion::where('actividad_id', $actividad->id)
            ->pluck('calificacion', 'alumno_id');

        return view('profesores.calificar', compact('actividad', 'grupo', 'alumnos', 'calificaciones'));
    }

    public function storeDirecto(Request $request, Actividad $actividad)
    {
        $grupo = $actividad->grupo;
        $this->authorizeProfesor($grupo);

        $request->validate([
            'calificaciones'   => 'required|array',
            'calificaciones.*' => 'nullable|numeric|min:0|max:100',
        ]);

        DB::transaction(function () use ($request, $actividad) {
            foreach ($request->calificaciones as $alumnoId => $valor) {
                if ($valor === null || $valor === '') {
                    Calificacion::where('actividad_id', $actividad->id)
                        ->where('alumno_id', $alumnoId)
                        ->delete();
                    continue;
                }

                Calificacion::updateOrCreate(
                    ['actividad_id' => $actividad->id, 'alumno_id' => $alumnoId],
                    ['calificacion' => $valor]
                );
            }
        });

        return redirect()->route('actividades.calificar', $actividad)
            ->with('success', 'Calificaciones guardadas correctamente.');
    }

    public function storeExcel(Request $request, Actividad $actividad)
    {
        $grupo = $actividad->grupo;
        $this->authorizeProfesor($grupo);

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        $rows   = Excel::toArray([], $request->file('archivo'));
        $sheet  = $rows[0] ?? [];

        $alumnosDelGrupo = $grupo->alumnosActivos()->pluck('id', 'matricula');

        $importados = 0;
        $errores    = [];

        DB::transaction(function () use ($sheet, $actividad, $alumnosDelGrupo, &$importados, &$errores) {
            foreach (array_slice($sheet, 1) as $i => $row) {
                $matricula = trim((string) ($row[0] ?? ''));
                $valor     = $row[1] ?? null;

                if ($matricula === '' || $valor === null || $valor === '') {
                    continue;
                }

                if (! isset($alumnosDelGrupo[$matricula])) {
                    $errores[] = "Fila " . ($i + 2) . ": matricula {$matricula} no esta en el grupo.";
                    continue;
                }

                if (! is_numeric($valor) || $valor < 0 || $valor > 100) {
                    $errores[] = "Fila " . ($i + 2) . ": calificacion invalida ({$valor}).";
                    continue;
                }

                Calificacion::updateOrCreate(
                    ['actividad_id' => $actividad->id, 'alumno_id' => $alumnosDelGrupo[$matricula]],
                    ['calificacion' => $valor]
                );
                $importados++;
            }
        });

        return redirect()->route('actividades.calificar', $actividad)
            ->with('success', "Se importaron {$importados} calificacion(es).")
            ->with('import_errors', $errores);
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
