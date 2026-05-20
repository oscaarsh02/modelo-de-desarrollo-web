<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Profesor;
use App\Services\TeamsCalificacionesParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamsImportController extends Controller
{
    /**
     * Formulario de importación de calificaciones desde Teams.
     */
    public function showForm(Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);
        $grupo->load(['materia', 'categorias.actividades', 'alumnosActivos']);
        return view('profesores.importar_teams', compact('grupo'));
    }

    /**
     * Parsea el archivo de Teams y muestra una vista previa antes de confirmar.
     */
    public function preview(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls',
        ]);

        $path   = $request->file('archivo')->getRealPath();
        $parser = new TeamsCalificacionesParser();
        $parsed = $parser->parse($path);

        if (empty($parsed['tareas'])) {
            return back()->with('error', 'No se encontraron tareas en el archivo de Teams.');
        }

        $grupo->load(['categorias.actividades', 'alumnosActivos']);

        // Cruzar alumnos de Teams con alumnos del grupo por correo o nombre
        $alumnosGrupo = $grupo->alumnosActivos()->get();

        $filas = collect($parsed['alumnos'])->map(function ($teamsAlumno) use ($alumnosGrupo) {
            $match = $alumnosGrupo->first(function ($a) use ($teamsAlumno) {
                return $teamsAlumno['correo'] && strtolower($a->correo ?? '') === $teamsAlumno['correo'];
            });

            if (! $match) {
                // Fallback: comparar por nombre (sin acentos, sin case)
                $match = $alumnosGrupo->first(function ($a) use ($teamsAlumno) {
                    return $this->similarNombre($a->nombre, $teamsAlumno['nombre']);
                });
            }

            return [
                'teams_nombre' => $teamsAlumno['nombre'],
                'teams_correo' => $teamsAlumno['correo'],
                'alumno_id'    => $match?->id,
                'alumno_nombre' => $match?->nombre,
                'matricula'    => $match?->matricula,
                'calificaciones' => $teamsAlumno['calificaciones'],
            ];
        });

        // Guardar datos parseados en sesión para la confirmación
        session(['teams_import_' . $grupo->id => [
            'tareas'   => $parsed['tareas'],
            'alumnos'  => $parsed['alumnos'],
        ]]);

        return view('profesores.importar_teams_preview', compact('grupo', 'filas', 'parsed'));
    }

    /**
     * Aplica la importación de calificaciones de Teams al grupo.
     * Auto-crea actividades si no existen.
     */
    public function store(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $request->validate([
            'mapeo'   => 'required|array',
            'mapeo.*' => 'nullable|string',
        ]);

        $sesionData = session('teams_import_' . $grupo->id);
        if (! $sesionData) {
            return back()->with('error', 'Sesión expirada. Sube el archivo nuevamente.');
        }

        $tareas  = $sesionData['tareas'];
        $alumnos = $sesionData['alumnos'];
        $mapeo   = $request->input('mapeo', []);   // [tareaNombre => actividad_id|'nueva'|'']

        $grupo->load(['categorias', 'alumnosActivos']);

        // Obtener o crear categoría por defecto para tareas de Teams
        $catDefault = $grupo->categorias()->firstOrCreate(
            ['nombre' => 'Teams'],
            ['ponderacion' => 0]
        );

        $actividadPorTarea = [];

        DB::transaction(function () use ($tareas, $mapeo, $grupo, $catDefault, $alumnos, &$actividadPorTarea) {
            // Resolver qué actividad corresponde a cada tarea de Teams
            foreach ($tareas as $tarea) {
                $asignacion = $mapeo[$tarea] ?? 'nueva';

                if ($asignacion === 'nueva' || $asignacion === '') {
                    // Crear actividad nueva en categoría Teams
                    $actividad = Actividad::firstOrCreate(
                        ['nombre' => $tarea, 'grupo_id' => $grupo->id],
                        ['categoria_id' => $catDefault->id]
                    );
                } elseif (is_numeric($asignacion)) {
                    $actividad = Actividad::find((int) $asignacion);
                } else {
                    continue;
                }

                if ($actividad) {
                    $actividadPorTarea[$tarea] = $actividad->id;
                }
            }

            // Registrar calificaciones
            $alumnosGrupo = $grupo->alumnosActivos()->get();

            foreach ($alumnos as $teamsAlumno) {
                // Buscar alumno del grupo por correo o nombre
                $alumno = $alumnosGrupo->first(function ($a) use ($teamsAlumno) {
                    return $teamsAlumno['correo'] && strtolower($a->correo ?? '') === $teamsAlumno['correo'];
                });

                if (! $alumno) {
                    $alumno = $alumnosGrupo->first(function ($a) use ($teamsAlumno) {
                        return $this->similarNombre($a->nombre, $teamsAlumno['nombre']);
                    });
                }

                if (! $alumno) {
                    continue;
                }

                foreach ($teamsAlumno['calificaciones'] as $tarea => $calificacion) {
                    if (! isset($actividadPorTarea[$tarea])) {
                        continue;
                    }

                    // Almacenar normalizada a 0-10 multiplicada por 10 → escala interna 0-100
                    Calificacion::updateOrCreate(
                        ['actividad_id' => $actividadPorTarea[$tarea], 'alumno_id' => $alumno->id],
                        ['calificacion' => $calificacion * 10]
                    );
                }
            }
        });

        session()->forget('teams_import_' . $grupo->id);

        return redirect()->route('grupos.show', $grupo)
            ->with('success', 'Calificaciones de Teams importadas correctamente.');
    }

    private function similarNombre(string $a, string $b): bool
    {
        $normalize = fn (string $s) => mb_strtolower(preg_replace('/\s+/', ' ', trim(iconv('UTF-8', 'ASCII//TRANSLIT', $s))));
        return $normalize($a) === $normalize($b);
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
