<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Asistencia;
use App\Models\Grupo;
use App\Models\Profesor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function scanner(Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        return view('profesores.asistencia', compact('grupo'));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'matricula' => 'required|string',
            'grupo_id'  => 'required|integer|exists:grupos,id',
        ]);

        $alumno = Alumno::where('matricula', $data['matricula'])->first();

        if (! $alumno) {
            return response()->json(['error' => 'Alumno no encontrado'], 404);
        }

        $inscrito = $alumno->grupos()
            ->where('grupos.id', $data['grupo_id'])
            ->wherePivotNull('baja_at')
            ->exists();

        if (! $inscrito) {
            return response()->json(['error' => 'El alumno no está inscrito en este grupo'], 403);
        }

        $fecha = now()->toDateString();
        $hora  = now()->toTimeString();

        $yaRegistrado = Asistencia::where('alumno_id', $alumno->id)
            ->where('grupo_id', $data['grupo_id'])
            ->where('fecha', $fecha)
            ->exists();

        if ($yaRegistrado) {
            return response()->json([
                'warning' => 'Asistencia ya registrada hoy',
                'alumno'  => $alumno->nombre,
            ]);
        }

        Asistencia::create([
            'alumno_id' => $alumno->id,
            'grupo_id'  => $data['grupo_id'],
            'fecha'     => $fecha,
            'hora'      => $hora,
        ]);

        return response()->json([
            'success' => true,
            'alumno'  => $alumno->nombre,
            'hora'    => substr($hora, 0, 5),
        ], 201);
    }

    public function manual(Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $alumnos = $grupo->alumnosActivos()->orderBy('nombre')->get();
        $fecha   = now()->toDateString();

        $presentes = Asistencia::where('grupo_id', $grupo->id)
            ->where('fecha', $fecha)
            ->pluck('alumno_id')
            ->toArray();

        return view('profesores.asistencia_manual', compact('grupo', 'alumnos', 'fecha', 'presentes'));
    }

    public function storeManual(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $request->validate([
            'fecha'      => 'required|date',
            'presentes'  => 'nullable|array',
            'presentes.*'=> 'integer|exists:alumnos,id',
        ]);

        $fecha     = $request->fecha;
        $presentesIds = $request->presentes ?? [];
        $todosIds  = $grupo->alumnosActivos()->pluck('alumnos.id')->toArray();
        $hora      = now()->toTimeString();

        foreach ($todosIds as $alumnoId) {
            if (in_array($alumnoId, $presentesIds)) {
                Asistencia::firstOrCreate(
                    ['alumno_id' => $alumnoId, 'grupo_id' => $grupo->id, 'fecha' => $fecha],
                    ['hora' => $hora]
                );
            } else {
                Asistencia::where('alumno_id', $alumnoId)
                    ->where('grupo_id', $grupo->id)
                    ->where('fecha', $fecha)
                    ->delete();
            }
        }

        $total = count($presentesIds);

        return redirect()->route('asistencia.manual', $grupo)
            ->with('success', "Lista guardada: {$total} presente(s) el {$fecha}.");
    }

    public function qr()
    {
        $alumno = Alumno::where('matricula', auth()->user()->matricula)->first();

        return view('alumno.qr', compact('alumno'));
    }

    public function historial()
    {
        $user = auth()->user();

        if ($user->role !== 'alumno') {
            abort(403);
        }

        $alumno = Alumno::where('matricula', $user->matricula)->first();

        $resumen = collect();

        if ($alumno) {
            $grupos = $alumno->gruposActivos()->with('materia')->get();

            $resumen = $grupos->map(function (Grupo $grupo) use ($alumno) {
                $asistencias = Asistencia::where('alumno_id', $alumno->id)
                    ->where('grupo_id', $grupo->id)
                    ->orderByDesc('fecha')
                    ->get();

                return [
                    'grupo'       => $grupo,
                    'total'       => $asistencias->count(),
                    'asistencias' => $asistencias,
                ];
            });
        }

        return view('alumno.asistencias', compact('alumno', 'resumen'));
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
