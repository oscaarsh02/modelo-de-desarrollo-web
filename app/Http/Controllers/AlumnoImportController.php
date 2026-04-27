<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class AlumnoImportController extends Controller
{
    public function showForm(Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);
        $grupo->load(['materia', 'alumnos']);
        return view('profesores.importar_alumnos', compact('grupo'));
    }

    public function import(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        $rows = Excel::toArray([], $request->file('archivo'));
        $sheet = $rows[0] ?? [];

        $imported = 0;
        $duplicates = 0;
        $importErrors = [];

        foreach (array_slice($sheet, 1) as $index => $row) {
            $nombre    = trim((string) ($row[0] ?? ''));
            $matricula = trim((string) ($row[1] ?? ''));

            if ($nombre === '' || $matricula === '') {
                continue;
            }

            try {
                $alumno = Alumno::firstOrCreate(
                    ['matricula' => $matricula],
                    ['nombre' => $nombre]
                );

                User::firstOrCreate(
                    ['matricula' => $matricula],
                    [
                        'name'     => $nombre,
                        'password' => Hash::make($matricula),
                        'role'     => 'alumno',
                    ]
                );

                if ($grupo->alumnos()->where('alumno_id', $alumno->id)->exists()) {
                    $duplicates++;
                } else {
                    $grupo->alumnos()->attach($alumno->id);
                    $imported++;
                }
            } catch (\Exception $e) {
                $importErrors[] = 'Fila ' . ($index + 2) . ': ' . $e->getMessage();
            }
        }

        return redirect()->route('grupos.importar.form', $grupo)
            ->with('import_success', "Se agregaron {$imported} alumno(s). {$duplicates} ya estaban en el grupo.")
            ->with('import_errors', $importErrors);
    }

    public function dashboard()
    {
        if (auth()->user()->role !== 'alumno') {
            abort(403);
        }

        $alumno = Alumno::where('matricula', auth()->user()->matricula)->first();

        $grupos = collect();
        $gruposConConcentrado = collect();

        if ($alumno) {
            $grupos = $alumno->gruposActivos()
                ->with(['materia', 'profesor', 'horarios.salon', 'categorias.actividades.calificaciones'])
                ->get();

            $gruposConConcentrado = $grupos->map(function ($grupo) use ($alumno) {
                $categorias = $grupo->categorias;

                $filaCats = $categorias->map(function ($cat) use ($alumno) {
                    $actividades = $cat->actividades->map(function ($act) use ($alumno) {
                        $cal = $act->calificaciones->firstWhere('alumno_id', $alumno->id);
                        return ['actividad' => $act, 'calificacion' => $cal?->calificacion];
                    });

                    $valores  = $actividades->whereNotNull('calificacion')->pluck('calificacion');
                    $promedio = $valores->isNotEmpty() ? round($valores->average(), 2) : null;

                    return ['categoria' => $cat, 'actividades' => $actividades, 'promedio' => $promedio];
                });

                $ponderadoTotal  = 0;
                $ponderacionAcum = 0;
                $filaCats->each(function ($f) use (&$ponderadoTotal, &$ponderacionAcum) {
                    if ($f['promedio'] !== null) {
                        $ponderadoTotal  += $f['promedio'] * ($f['categoria']->ponderacion / 100);
                        $ponderacionAcum += $f['categoria']->ponderacion;
                    }
                });

                return [
                    'grupo'             => $grupo,
                    'categorias'        => $filaCats,
                    'promedio_ponderado' => $ponderacionAcum > 0 ? round($ponderadoTotal, 2) : null,
                ];
            });
        }

        return view('alumno.dashboard', compact('alumno', 'gruposConConcentrado'));
    }

    public function baja(Request $request, Grupo $grupo)
    {
        if (auth()->user()->role !== 'alumno') {
            abort(403);
        }

        $alumno = Alumno::where('matricula', auth()->user()->matricula)->first();

        if (! $alumno) {
            abort(403);
        }

        $pivot = $alumno->grupos()
            ->where('grupo_id', $grupo->id)
            ->wherePivotNull('baja_at')
            ->first();

        if (! $pivot) {
            return redirect()->route('alumno.dashboard')
                ->with('error', 'No estas inscrito en este grupo o ya diste de baja.');
        }

        $alumno->grupos()->updateExistingPivot($grupo->id, ['baja_at' => now()]);

        $nombreMateria = $grupo->materia->nombre ?? 'la materia';

        return redirect()->route('alumno.dashboard')
            ->with('baja_success', "Te diste de baja de \"{$nombreMateria}\". Esta accion es permanente.");
    }

    public function reinscribir(Grupo $grupo, \App\Models\Alumno $alumno)
    {
        $this->authorizeProfesor($grupo);

        $pivot = $alumno->grupos()
            ->where('grupo_id', $grupo->id)
            ->wherePivotNotNull('baja_at')
            ->first();

        if (! $pivot) {
            return redirect()->route('grupos.show', $grupo)
                ->with('error', 'El alumno no está dado de baja en este grupo.');
        }

        $alumno->grupos()->updateExistingPivot($grupo->id, ['baja_at' => null]);

        return redirect()->route('grupos.show', $grupo)
            ->with('success', "{$alumno->nombre} ha sido reinscrito al grupo.");
    }

    private function authorizeProfesor(Grupo $grupo): void
    {
        $user = auth()->user();

        if ($user->role !== 'profesor') {
            abort(403, 'Solo profesores pueden realizar esta accion.');
        }

        $profesor = Profesor::where('user_id', $user->id)
            ->orWhere('matricula', $user->matricula)
            ->first();

        if (! $profesor || $grupo->profesor_id !== $profesor->id) {
            abort(403, 'No tienes permiso para gestionar este grupo.');
        }
    }
}
