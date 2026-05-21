<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;

class TeamsCalificacionesParser
{
    private int $rowCount = 0;

    public function lastRowCount(): int
    {
        return $this->rowCount;
    }

    public function parse(string $path, string $extension = 'xlsx'): array
    {
        $readerType = match($extension) {
            'csv'  => \Maatwebsite\Excel\Excel::CSV,
            'xls'  => \Maatwebsite\Excel\Excel::XLS,
            default => \Maatwebsite\Excel\Excel::XLSX,
        };

        $sheets = Excel::toArray([], $path, null, $readerType);
        $rows   = $sheets[0] ?? [];
        $this->rowCount = count($rows);

        if (empty($rows)) {
            return ['materia' => '', 'tareas' => [], 'alumnos' => []];
        }

        // Fila 0: título (col 3 = nombre del curso)
        $titulo = '';
        if (isset($rows[0][3]) && $rows[0][3]) {
            $titulo = trim((string) $rows[0][3]);
        }

        // Fila 1: cabeceras (índices base 0)
        // [3]=Nombre completo, [4]=Nombre, [5]=Apellidos, [6]=Correo,
        // [7]=Tarea, [10]=Estado, [12]=Puntos, [13]=Puntos máximos

        // Construir mapa de tareas únicas y registros por alumno
        $tareas   = [];     // nombre de tarea en orden de aparición
        $alumnos  = [];     // email => [nombre, correo, calificaciones[tarea]=val]

        foreach (array_slice($rows, 2) as $row) {
            $nombreCompleto = trim((string) ($row[3] ?? ''));
            $correo         = strtolower(trim((string) ($row[6] ?? '')));
            $tareaNombre    = trim((string) ($row[7] ?? ''));
            $puntos         = $row[12] ?? null;
            $puntosMax      = $row[13] ?? null;

            if ($nombreCompleto === '' || $tareaNombre === '') {
                continue;
            }

            // Registrar tarea
            if (! in_array($tareaNombre, $tareas, true)) {
                $tareas[] = $tareaNombre;
            }

            // Registrar alumno
            $key = $correo ?: $nombreCompleto;
            if (! isset($alumnos[$key])) {
                $alumnos[$key] = [
                    'nombre' => $nombreCompleto,
                    'correo' => $correo,
                    'calificaciones' => [],
                ];
            }

            // Normalizar calificación a escala 0-10
            $calificacion = $this->normalizar($puntos, $puntosMax);
            $alumnos[$key]['calificaciones'][$tareaNombre] = $calificacion;
        }

        return [
            'materia' => $titulo,
            'tareas'  => $tareas,
            'alumnos' => array_values($alumnos),
        ];
    }

    /**
     * Convierte puntos/puntosMaximos a escala 0-10.
     * Si puntos es null (no calificado), retorna 0.
     */
    public function normalizar($puntos, $puntosMax): float
    {
        if ($puntosMax === null || (float) $puntosMax == 0) {
            return 0.0;
        }

        if ($puntos === null || $puntos === '') {
            return 0.0;
        }

        $escala = ((float) $puntos / (float) $puntosMax) * 10;

        return round(min(max($escala, 0), 10), 2);
    }
}
