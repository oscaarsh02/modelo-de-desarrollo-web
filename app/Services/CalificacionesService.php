<?php

namespace App\Services;

use App\Models\Asistencia;
use App\Models\Categoria;
use App\Models\Grupo;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class CalificacionesService
{
    public function categoriaEsAsistencia(Categoria $categoria): bool
    {
        return Str::contains(Str::lower(Str::ascii($categoria->nombre)), 'asistencia');
    }

    public function resumenAsistencia(Grupo $grupo, int $alumnoId): array
    {
        $totalSesiones = Asistencia::where('grupo_id', $grupo->id)
            ->distinct('fecha')
            ->count('fecha');

        if ($totalSesiones === 0) {
            return [
                'presentes' => 0,
                'total' => 0,
                'calificacion' => null,
            ];
        }

        $presentes = Asistencia::where('grupo_id', $grupo->id)
            ->where('alumno_id', $alumnoId)
            ->count();

        return [
            'presentes' => $presentes,
            'total' => $totalSesiones,
            'calificacion' => round(($presentes / $totalSesiones) * 100, 2),
        ];
    }

    public function construirConcentrado(Grupo $grupo, Collection $alumnos, Collection $categorias): Collection
    {
        return $alumnos->map(function ($alumno) use ($grupo, $categorias) {
            $filaCats = $categorias->map(function ($cat) use ($grupo, $alumno) {
                if ($this->categoriaEsAsistencia($cat)) {
                    $resumen = $this->resumenAsistencia($grupo, $alumno->id);
                    $actividades = collect([
                        [
                            'actividad' => (object) ['nombre' => 'Asistencias registradas'],
                            'calificacion' => $resumen['calificacion'],
                            'resumen_asistencia' => $resumen,
                            'solo_lectura' => true,
                        ],
                    ]);

                    return [
                        'categoria' => $cat,
                        'actividades' => $actividades,
                        'promedio' => $resumen['calificacion'],
                    ];
                }

                $actividades = $cat->actividades->map(function ($act) use ($alumno) {
                    $cal = $act->calificaciones->firstWhere('alumno_id', $alumno->id);
                    return ['actividad' => $act, 'calificacion' => $cal?->calificacion];
                });

                $valores = $actividades->whereNotNull('calificacion')->pluck('calificacion');
                $promedio = $valores->isNotEmpty() ? round($valores->average(), 2) : null;

                return ['categoria' => $cat, 'actividades' => $actividades, 'promedio' => $promedio];
            });

            $ponderadoTotal = 0;
            $ponderacionAcum = 0;
            $filaCats->each(function ($f) use (&$ponderadoTotal, &$ponderacionAcum) {
                if ($f['promedio'] !== null) {
                    $ponderadoTotal += $f['promedio'] * ($f['categoria']->ponderacion / 100);
                    $ponderacionAcum += $f['categoria']->ponderacion;
                }
            });

            return [
                'alumno' => $alumno,
                'categorias' => $filaCats,
                'promedio_ponderado' => $ponderacionAcum > 0 ? round($ponderadoTotal, 2) : null,
            ];
        });
    }
}
