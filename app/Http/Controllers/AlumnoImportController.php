<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Models\User;
use App\Services\HtmListaParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AlumnoImportController extends Controller
{
    public function showForm(Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);
        $grupo->load(['materia', 'alumnos' => fn ($q) => $q->withPivot('baja_at')]);
        return view('profesores.importar_alumnos', compact('grupo'));
    }

    /**
     * 1ª pasada: importar HTM y dar de alta alumnos nuevos.
     */
    public function import(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $request->validate([
            'archivo' => 'required|file|mimes:htm,html',
        ]);

        $html    = file_get_contents($request->file('archivo')->getRealPath());
        $parser  = new HtmListaParser();
        $parsed  = $parser->parse($html);
        $alumnos = $parsed['alumnos'];

        if (empty($alumnos)) {
            return back()->with('import_errors', ['No se encontraron alumnos en el archivo HTM.']);
        }

        $importados  = 0;
        $duplicados  = 0;
        $errores     = [];

        foreach ($alumnos as $data) {
            try {
                $alumno = Alumno::firstOrCreate(
                    ['matricula' => $data['matricula']],
                    ['nombre' => $data['nombre'], 'correo' => $data['correo']]
                );

                // Actualizar correo si ahora lo tenemos y antes no había
                if ($data['correo'] && ! $alumno->correo) {
                    $alumno->update(['correo' => $data['correo']]);
                }

                User::firstOrCreate(
                    ['matricula' => $data['matricula']],
                    [
                        'name'     => $data['nombre'],
                        'password' => Hash::make($data['matricula']),
                        'role'     => 'alumno',
                    ]
                );

                $pivot = $grupo->alumnos()->where('alumno_id', $alumno->id)->first();

                if ($pivot) {
                    // Si estaba dado de baja, reactivar
                    if ($pivot->pivot->baja_at) {
                        $grupo->alumnos()->updateExistingPivot($alumno->id, ['baja_at' => null]);
                        $importados++;
                    } else {
                        $duplicados++;
                    }
                } else {
                    $grupo->alumnos()->attach($alumno->id);
                    $importados++;
                }
            } catch (\Exception $e) {
                $errores[] = "Matrícula {$data['matricula']}: {$e->getMessage()}";
            }
        }

        return redirect()->route('grupos.importar.form', $grupo)
            ->with('import_success', "Se dieron de alta {$importados} alumno(s). {$duplicados} ya estaban registrados.")
            ->with('import_errors', $errores);
    }

    /**
     * 2ª pasada: conciliación de lista.
     * Compara el HTM con la lista actual y muestra diferencias para confirmar.
     */
    public function previewConciliacion(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $request->validate([
            'archivo' => 'required|file|mimes:htm,html',
        ]);

        $html    = file_get_contents($request->file('archivo')->getRealPath());
        $parser  = new HtmListaParser();
        $parsed  = $parser->parse($html);
        $htmAlumnos = collect($parsed['alumnos'])->keyBy('matricula');

        $grupo->load(['alumnos' => fn ($q) => $q->withPivot('baja_at')]);

        $activos = $grupo->alumnosActivos()->get()->keyBy('matricula');
        $bajas   = $grupo->alumnos()->wherePivotNotNull('baja_at')->get()->keyBy('matricula');

        // Alumnos en HTM pero no en el grupo → alta nueva
        $nuevos = $htmAlumnos->filter(fn ($a) => ! $activos->has($a['matricula']) && ! $bajas->has($a['matricula']));

        // Alumnos activos en grupo pero no en HTM → posible baja
        $posiblesBajas = $activos->filter(fn ($a) => ! $htmAlumnos->has($a->matricula));

        // Alumnos que en el HTM aparecen pero estaban dados de baja → reactivaciones
        $reactivaciones = $bajas->filter(fn ($a) => $htmAlumnos->has($a->matricula));

        // Alumnos sin cambio
        $sinCambio = $activos->filter(fn ($a) => $htmAlumnos->has($a->matricula));

        return view('profesores.conciliar_lista', compact(
            'grupo', 'nuevos', 'posiblesBajas', 'reactivaciones', 'sinCambio'
        ));
    }

    /**
     * Aplica la conciliación confirmada por el profesor.
     */
    public function aplicarConciliacion(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $nuevas         = $request->input('nuevos', []);
        $bajas          = $request->input('bajas', []);
        $reactivaciones = $request->input('reactivar', []);

        $altasCount   = 0;
        $bajasCount   = 0;
        $reactivCount = 0;

        // Dar de alta alumnos nuevos (matriculas confirmadas)
        foreach ($nuevas as $matricula) {
            $alumno = Alumno::where('matricula', $matricula)->first();
            if (! $alumno) {
                continue;
            }
            if (! $grupo->alumnos()->where('alumno_id', $alumno->id)->exists()) {
                $grupo->alumnos()->attach($alumno->id);
                $altasCount++;
            }
        }

        // Marcar bajas
        foreach ($bajas as $matricula) {
            $alumno = Alumno::where('matricula', $matricula)->first();
            if (! $alumno) {
                continue;
            }
            $grupo->alumnos()->updateExistingPivot($alumno->id, ['baja_at' => now()]);
            $bajasCount++;
        }

        // Reactivar
        foreach ($reactivaciones as $matricula) {
            $alumno = Alumno::where('matricula', $matricula)->first();
            if (! $alumno) {
                continue;
            }
            $grupo->alumnos()->updateExistingPivot($alumno->id, ['baja_at' => null]);
            $reactivCount++;
        }

        return redirect()->route('grupos.importar.form', $grupo)
            ->with('import_success', "Conciliación aplicada: {$altasCount} alta(s), {$bajasCount} baja(s), {$reactivCount} reactivación(es).");
    }

    // ─── Alumno dashboard ────────────────────────────────────────────────────

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
                    'grupo'              => $grupo,
                    'categorias'         => $filaCats,
                    'promedio_ponderado' => $ponderacionAcum > 0 ? round($ponderadoTotal, 2) : null,
                ];
            });
        }

        return view('alumno.dashboard', compact('alumno', 'gruposConConcentrado'));
    }

    public function baja(Grupo $grupo)
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
