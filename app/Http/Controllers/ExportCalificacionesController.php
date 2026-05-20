<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Grupo;
use App\Models\Profesor;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ExportCalificacionesController extends Controller
{
    /**
     * Vista previa con tabla editable de calificaciones.
     */
    public function preview(Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $grupo->load([
            'materia',
            'categorias.actividades.calificaciones',
            'alumnosActivos',
        ]);

        $categorias = $grupo->categorias;
        $alumnos    = $grupo->alumnosActivos()->orderBy('nombre')->get();

        // Construir matriz de calificaciones [alumno_id][actividad_id] = calificacion (0-10)
        $matriz = [];
        foreach ($alumnos as $alumno) {
            foreach ($categorias as $cat) {
                foreach ($cat->actividades as $act) {
                    $cal = $act->calificaciones->firstWhere('alumno_id', $alumno->id);
                    $valor = $cal ? round($cal->calificacion / 10, 2) : null;
                    $matriz[$alumno->id][$act->id] = $valor;
                }
            }
        }

        return view('profesores.exportar_calificaciones', compact('grupo', 'categorias', 'alumnos', 'matriz'));
    }

    /**
     * Guarda ajustes manuales y redirige de vuelta al preview.
     */
    public function guardarAjustes(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $ajustes = $request->input('calificaciones', []);

        foreach ($ajustes as $alumnoId => $actividades) {
            foreach ($actividades as $actividadId => $valor) {
                if ($valor === null || $valor === '') {
                    Calificacion::where('actividad_id', $actividadId)
                        ->where('alumno_id', $alumnoId)
                        ->delete();
                    continue;
                }

                $valorInterno = round((float) $valor * 10, 2);

                Calificacion::updateOrCreate(
                    ['actividad_id' => $actividadId, 'alumno_id' => $alumnoId],
                    ['calificacion' => min(max($valorInterno, 0), 100)]
                );
            }
        }

        return redirect()->route('grupos.exportar', $grupo)
            ->with('success', 'Ajustes guardados.');
    }

    /**
     * Genera y descarga el archivo Excel con el formato de calificaciones.
     */
    public function download(Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $grupo->load([
            'materia',
            'categorias.actividades.calificaciones',
            'alumnosActivos',
        ]);

        $spreadsheet = $this->generarExcel($grupo);

        $writer   = new Xlsx($spreadsheet);
        $filename = 'Calificaciones_' . ($grupo->materia->clave ?? 'grupo') . '_' . $grupo->nombre . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control'       => 'max-age=0',
        ]);
    }

    private function generarExcel(Grupo $grupo): Spreadsheet
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $nombreHoja = substr($grupo->materia->nombre ?? $grupo->nombre, 0, 31);
        $sheet->setTitle($nombreHoja);

        $categorias = $grupo->categorias;
        $alumnos    = $grupo->alumnosActivos()->orderBy('nombre')->get();

        // ── Calcular columnas ─────────────────────────────────────────────
        // Columnas A=1, B=2, C=3, luego una por actividad
        $colDatos = 4; // D = primera actividad
        $actColumnas = []; // actividad_id => índice de columna

        foreach ($categorias as $cat) {
            foreach ($cat->actividades as $act) {
                $actColumnas[$act->id] = $colDatos++;
            }
        }

        $colFinal = $colDatos; // columna de calificación final ponderada

        // ── Fila 1: Título ────────────────────────────────────────────────
        $titulo = ($grupo->materia->nombre ?? 'Calificaciones') . ' - ' . $grupo->nombre;
        $sheet->setCellValueByColumnAndRow(2, 1, $titulo);
        $sheet->mergeCellsByColumnAndRow(2, 1, $colFinal, 1);
        $sheet->getStyleByColumnAndRow(2, 1)->getFont()->setBold(true)->setSize(14);

        // ── Fila 2: Ponderaciones por categoría ───────────────────────────
        $sheet->setCellValueByColumnAndRow(1, 2, 'Ponderación');
        $sheet->getStyleByColumnAndRow(1, 2)->getFont()->setBold(true);
        $catColStart = 4;
        foreach ($categorias as $cat) {
            $numActs = $cat->actividades->count();
            if ($numActs === 0) {
                continue;
            }
            $sheet->setCellValueByColumnAndRow($catColStart, 2, $cat->ponderacion / 100);
            if ($numActs > 1) {
                $sheet->mergeCellsByColumnAndRow($catColStart, 2, $catColStart + $numActs - 1, 2);
            }
            $sheet->getStyleByColumnAndRow($catColStart, 2, $catColStart + $numActs - 1, 2)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $catColStart += $numActs;
        }

        // ── Fila 3: Nombres de categorías ─────────────────────────────────
        $sheet->setCellValueByColumnAndRow(1, 3, 'Categoría');
        $sheet->getStyleByColumnAndRow(1, 3)->getFont()->setBold(true);
        $catColStart = 4;
        foreach ($categorias as $cat) {
            $numActs = $cat->actividades->count();
            if ($numActs === 0) {
                continue;
            }
            $sheet->setCellValueByColumnAndRow($catColStart, 3, $cat->nombre);
            if ($numActs > 1) {
                $sheet->mergeCellsByColumnAndRow($catColStart, 3, $catColStart + $numActs - 1, 3);
            }
            $sheet->getStyleByColumnAndRow($catColStart, 3, $catColStart + $numActs - 1, 3)
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $catColStart += $numActs;
        }

        // ── Fila 4: Cabeceras ──────────────────────────────────────────────
        $sheet->setCellValueByColumnAndRow(1, 4, '#');
        $sheet->setCellValueByColumnAndRow(2, 4, 'Nombre de Alumno');
        $sheet->setCellValueByColumnAndRow(3, 4, 'ID');
        foreach ($categorias as $cat) {
            foreach ($cat->actividades as $act) {
                $col = $actColumnas[$act->id];
                $sheet->setCellValueByColumnAndRow($col, 4, $act->nombre);
                $sheet->getColumnDimensionByColumn($col)->setWidth(18);
            }
        }
        $sheet->setCellValueByColumnAndRow($colFinal, 4, 'Cal. Final');

        // Estilo de cabecera
        $sheet->getStyleByColumnAndRow(1, 4, $colFinal, 4)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── Filas de datos ─────────────────────────────────────────────────
        $rowNum = 5;
        $num    = 1;
        foreach ($alumnos as $alumno) {
            $sheet->setCellValueByColumnAndRow(1, $rowNum, $num++);
            $sheet->setCellValueByColumnAndRow(2, $rowNum, $alumno->nombre);
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $alumno->matricula);

            foreach ($categorias as $cat) {
                foreach ($cat->actividades as $act) {
                    $col = $actColumnas[$act->id];
                    $cal = $act->calificaciones->firstWhere('alumno_id', $alumno->id);
                    $valor = $cal ? round($cal->calificacion / 10, 2) : 0;
                    $sheet->setCellValueByColumnAndRow($col, $rowNum, $valor);
                    $sheet->getStyleByColumnAndRow($col, $rowNum)->getNumberFormat()->setFormatCode('0.00');
                }
            }

            // Fórmula de calificación final ponderada
            $formula = $this->construirFormula($categorias, $actColumnas, $colFinal, $rowNum);
            if ($formula) {
                $sheet->setCellValueByColumnAndRow($colFinal, $rowNum, $formula);
                $sheet->getStyleByColumnAndRow($colFinal, $rowNum)->getNumberFormat()->setFormatCode('0.00');
            }

            // Alternar color de fila
            if ($rowNum % 2 === 0) {
                $sheet->getStyleByColumnAndRow(1, $rowNum, $colFinal, $rowNum)
                    ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $rowNum++;
        }

        // ── Ajuste de columnas fijas ────────────────────────────────────────
        $sheet->getColumnDimensionByColumn(1)->setWidth(5);
        $sheet->getColumnDimensionByColumn(2)->setWidth(32);
        $sheet->getColumnDimensionByColumn(3)->setWidth(14);
        $sheet->getColumnDimensionByColumn($colFinal)->setWidth(12);

        // Bordes en área de datos
        if ($rowNum > 5) {
            $sheet->getStyleByColumnAndRow(1, 4, $colFinal, $rowNum - 1)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']],
                ],
            ]);
        }

        return $spreadsheet;
    }

    /**
     * Construye la fórmula de calificación final ponderada para una fila.
     * Suma: AVERAGE(columnas_cat) * ponderacion para cada categoría.
     */
    private function construirFormula($categorias, array $actColumnas, int $colFinal, int $row): string
    {
        $partes = [];
        foreach ($categorias as $cat) {
            $actsIds = $cat->actividades->pluck('id')->toArray();
            if (empty($actsIds)) {
                continue;
            }

            $cols = array_map(fn ($id) => $actColumnas[$id], $actsIds);
            sort($cols);

            $peso = $cat->ponderacion / 100;

            if (count($cols) === 1) {
                $ref    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols[0]) . $row;
                $partes[] = "{$ref}*{$peso}";
            } else {
                $inicio = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(min($cols)) . $row;
                $fin    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(max($cols)) . $row;
                $partes[] = "AVERAGE({$inicio}:{$fin})*{$peso}";
            }
        }

        if (empty($partes)) {
            return '';
        }

        return '=' . implode('+', $partes);
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
