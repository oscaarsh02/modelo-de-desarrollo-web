<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Grupo;
use App\Models\Horario;
use App\Models\Profesor;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Smalot\PdfParser\Parser;

class HorariosController extends Controller
{
    /**
     * Cache de profesores por nombre canonico para evitar duplicados por variaciones.
     *
     * @var array<string, Profesor>
     */
    private array $profesoresPorCanon = [];

    /**
     * Mostrar listado de horarios.
     */
    public function index(Request $request)
    {
        if (auth()->check() && auth()->user()->role === 'profesor') {
            return redirect()->route('profesor.grupos');
        }

        $nrcPaginator = Horario::query()
            ->select('nrc')
            ->distinct()
            ->orderBy('nrc')
            ->paginate(12, ['nrc'])
            ->withQueryString();

        $nrcsPagina = $nrcPaginator->pluck('nrc')->all();

        $horariosPagina = Horario::with(['grupo.materia', 'profesor', 'salon'])
            ->whereIn('nrc', $nrcsPagina)
            ->orderBy('nrc')
            ->orderBy('hora')
            ->get();

        $horariosAgrupados = $horariosPagina->groupBy('nrc');

        return view('horarios.index', [
            'horariosAgrupados' => $horariosAgrupados,
            'nrcPaginator' => $nrcPaginator,
            'totalBloques' => Horario::count(),
            'totalNrc' => Horario::query()->select('nrc')->distinct()->count('nrc'),
        ]);
    }

    /**
     * Mostrar formulario de carga.
     */
    public function create()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Acceso denegado. Solo los administradores pueden subir PDFs.');
        }

        return view('horarios.create');
    }

    /**
     * Procesar el PDF y guardar los datos.
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Acceso denegado. Solo los administradores pueden subir PDFs.');
        }

        $request->validate([
            'pdf' => 'required|mimes:pdf|max:10000',
        ], [
            'pdf.required' => 'Por favor selecciona un archivo PDF',
            'pdf.mimes' => 'El archivo debe ser un PDF',
            'pdf.max' => 'El archivo no debe pesar mas de 10MB',
        ]);

        try {
            $file = $request->file('pdf');
            $parser = new Parser();
            $pdf = $parser->parseFile($file->path());
            $text = $pdf->getText();

            $datos = $this->parsearPDF($text);

            if (empty($datos)) {
                return back()->with('error', 'No se encontraron datos en el PDF');
            }

            $resultado = $this->procesarDatos($datos);

            return back()->with('success', $this->construirMensajeResultado($resultado));
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Parsear texto del PDF en filas estructuradas.
     */
    private function parsearPDF(string $text): array
    {
        $datos = [];
        $lineas = preg_split('/\R/u', $text);

        // Formato real del PDF: NRC CLAVE NUM MATERIA SECC DIA HORA PROFESOR SALON
        $pattern = '/^(\d{5})\s+([A-Z]{3,6})\s+(\d{3})\s+(.+?)\s+([A-Z0-9]{2,5})\s+([LAMJVS])\s+(\d{4}\s*-\s*\d{4})\s+(.+?)\s+([0-9A-Z]+\/[0-9A-Z]+)$/u';

        foreach ($lineas as $linea) {
            // Smalot inserta tabs en medio de apellidos y horas (ej: 0700-08\t59).
            $linea = str_replace("\t", ' ', trim($linea));
            $linea = preg_replace('/\s+/u', ' ', $linea);
            $linea = preg_replace('/(\d{4})\s*-\s*(\d{2})\s+(\d{2})/u', '$1-$2$3', $linea);
            $linea = preg_replace('/(\d{4})\s*-\s*(\d)\s+(\d{3})/u', '$1-$2$3', $linea);
            $linea = preg_replace('/(\d{4})\s*-\s*(\d{3})\s+(\d)/u', '$1-$2$3', $linea);

            if (empty($linea) || !preg_match($pattern, $linea, $match)) {
                continue;
            }

            $datos[] = [
                'nrc' => $match[1],
                'clave' => trim($match[2] . $match[3]),
                'materia_nombre' => trim($match[4]),
                'grupo' => trim($match[5]) ?: null,
                'dia' => trim($match[6]),
                'hora' => preg_replace('/\s*/u', '', trim($match[7])),
                'profesor' => trim($match[8]) ?: null,
                'salon' => trim($match[9]),
            ];
        }

        return $datos;
    }

    /**
     * Procesar y guardar los datos en la BD.
     */
    private function procesarDatos(array $datos): array
    {
        $resultado = [
            'materias' => 0,
            'grupos' => 0,
            'horarios' => 0,
            'salones' => 0,
            'profesores' => 0,
            'profesores_unificados' => 0,
            'grupos_asignados' => 0,
            'conflictos_grupo_profesor' => 0,
            'usuarios_profesor_creados' => 0,
            'credenciales_nuevas' => [],
        ];

        // Limpia duplicados historicos por variaciones del nombre (ZE NTENO / ZENT ENO / ZENTENO).
        $resultado['profesores_unificados'] = $this->consolidarProfesoresDuplicadosPorCanon();

        $gruposCreados = [];

        foreach ($datos as $fila) {
            $nombreMateria = $fila['materia_nombre'] ?: ('Materia ' . $fila['clave']);

            $materia = Materia::firstOrCreate(
                ['clave' => $fila['clave']],
                ['nombre' => $nombreMateria]
            );

            if ($materia->wasRecentlyCreated) {
                $resultado['materias']++;
            } elseif (!empty($fila['materia_nombre']) && str_starts_with($materia->nombre, 'Materia ')) {
                $materia->update(['nombre' => $fila['materia_nombre']]);
            }

            $nombreGrupo = $fila['grupo'] ?: $fila['clave'];
            $grupoKey = $materia->id . '_' . $nombreGrupo;

            if (!isset($gruposCreados[$grupoKey])) {
                $grupo = Grupo::firstOrCreate([
                    'nombre' => $nombreGrupo,
                    'materia_id' => $materia->id,
                ]);

                if ($grupo->wasRecentlyCreated) {
                    $resultado['grupos']++;
                }

                $gruposCreados[$grupoKey] = $grupo;
            } else {
                $grupo = $gruposCreados[$grupoKey];
            }

            $salon = Salon::firstOrCreate(['nombre' => $fila['salon']]);
            if ($salon->wasRecentlyCreated) {
                $resultado['salones']++;
            }

            $profesor = null;
            if (!empty($fila['profesor'])) {
                $profesor = $this->obtenerOCrearProfesor($fila['profesor'], $resultado);
                $this->sincronizarCuentaProfesor($profesor, $resultado);

                $estadoAsignacion = $this->asignarProfesorAGrupo($grupo, $profesor);
                if ($estadoAsignacion === 'asignado') {
                    $resultado['grupos_asignados']++;
                } elseif ($estadoAsignacion === 'conflicto') {
                    $resultado['conflictos_grupo_profesor']++;
                }
            }

            $horario = Horario::updateOrCreate(
                [
                    'nrc' => $fila['nrc'],
                    'dia' => $fila['dia'],
                    'hora' => $fila['hora'],
                ],
                [
                    'grupo_id' => $grupo->id,
                    'salon_id' => $salon->id,
                    'profesor_id' => $profesor ? $profesor->id : null,
                ]
            );

            if ($horario->wasRecentlyCreated) {
                $resultado['horarios']++;
            }
        }

        return $resultado;
    }

    /**
     * Armar mensaje de resultado para el admin.
     */
    private function construirMensajeResultado(array $resultado): string
    {
        $lineas = [
            'Archivo cargado exitosamente.',
            '',
            'Resultados:',
            "- Materias creadas/encontradas: {$resultado['materias']}",
            "- Grupos creados/encontrados: {$resultado['grupos']}",
            "- Horarios cargados: {$resultado['horarios']}",
            "- Salones creados/encontrados: {$resultado['salones']}",
            "- Profesores detectados: {$resultado['profesores']}",
            "- Profesores unificados por duplicado: {$resultado['profesores_unificados']}",
            "- Grupos vinculados con profesor: {$resultado['grupos_asignados']}",
            "- Usuarios profesor nuevos: {$resultado['usuarios_profesor_creados']}",
        ];

        if ($resultado['conflictos_grupo_profesor'] > 0) {
            $lineas[] = "- Conflictos grupo-profesor detectados: {$resultado['conflictos_grupo_profesor']}";
        }

        if (!empty($resultado['credenciales_nuevas'])) {
            $lineas[] = '';
            $lineas[] = 'Credenciales generadas (profesores nuevos):';

            foreach ($resultado['credenciales_nuevas'] as $credencial) {
                $lineas[] = "- {$credencial['nombre']} | Matricula: {$credencial['matricula']} | Password: {$credencial['password']}";
            }
        }

        return implode("\n", $lineas);
    }

    /**
     * Obtener o crear profesor con matricula unica.
     */
    private function obtenerOCrearProfesor(string $nombre, array &$resultado): Profesor
    {
        $nombre = $this->normalizarNombreProfesor($nombre);
        $canon = $this->nombreCanonicoProfesor($nombre);

        $profesor = $this->buscarProfesorPorCanon($canon);
        if ($profesor) {
            if ($this->puntajeNombreProfesor($nombre) < $this->puntajeNombreProfesor($profesor->nombre)) {
                $profesor->update(['nombre' => $nombre]);
            }
            return $profesor;
        }

        // Evita colisiones como PROF_BAUTISTA para distintos apellidos.
        $baseCanon = $canon !== '' ? $canon : $nombre;
        $base = 'PROF_' . strtoupper(substr(md5($baseCanon), 0, 10));
        $matricula = $base;
        $contador = 1;

        while (Profesor::where('matricula', $matricula)->exists()) {
            $matricula = $base . '_' . $contador;
            $contador++;
        }

        $profesor = Profesor::create([
            'nombre' => $nombre,
            'matricula' => $matricula,
        ]);

        if ($canon !== '') {
            $this->profesoresPorCanon[$canon] = $profesor;
        }

        $resultado['profesores']++;

        return $profesor;
    }

    /**
     * Buscar profesor existente por nombre canonico.
     */
    private function buscarProfesorPorCanon(string $canon): ?Profesor
    {
        if ($canon === '') {
            return null;
        }

        if (empty($this->profesoresPorCanon)) {
            $this->cargarIndiceProfesoresCanonico();
        }

        return $this->profesoresPorCanon[$canon] ?? null;
    }

    /**
     * Cargar indice de profesores existentes y elegir el mejor nombre por clave canonica.
     */
    private function cargarIndiceProfesoresCanonico(): void
    {
        $this->profesoresPorCanon = [];

        foreach (Profesor::all() as $profesor) {
            $canon = $this->nombreCanonicoProfesor((string) $profesor->nombre);
            if ($canon === '') {
                continue;
            }

            if (!isset($this->profesoresPorCanon[$canon])) {
                $this->profesoresPorCanon[$canon] = $profesor;
                continue;
            }

            $actual = $this->profesoresPorCanon[$canon];
            if ($this->puntajeNombreProfesor($profesor->nombre) < $this->puntajeNombreProfesor($actual->nombre)) {
                $this->profesoresPorCanon[$canon] = $profesor;
            }
        }
    }

    /**
     * Unificar profesores duplicados segun nombre canonico.
     */
    private function consolidarProfesoresDuplicadosPorCanon(): int
    {
        $profesores = Profesor::orderBy('id')->get();
        $grupos = [];

        foreach ($profesores as $profesor) {
            $canon = $this->nombreCanonicoProfesor((string) $profesor->nombre);
            if ($canon === '') {
                continue;
            }
            $grupos[$canon][] = $profesor;
        }

        $eliminados = 0;

        foreach ($grupos as $grupoCanonico) {
            if (count($grupoCanonico) < 2) {
                continue;
            }

            usort($grupoCanonico, function (Profesor $a, Profesor $b) {
                $scoreA = $this->puntajeNombreProfesor($a->nombre);
                $scoreB = $this->puntajeNombreProfesor($b->nombre);

                if ($scoreA !== $scoreB) {
                    return $scoreA <=> $scoreB;
                }

                return $a->id <=> $b->id;
            });

            $principal = $grupoCanonico[0];
            $nombreNormalizado = $this->normalizarNombreProfesor($principal->nombre);
            if ($principal->nombre !== $nombreNormalizado) {
                $principal->update(['nombre' => $nombreNormalizado]);
            }

            foreach (array_slice($grupoCanonico, 1) as $duplicado) {
                DB::transaction(function () use ($principal, $duplicado, &$eliminados) {
                    Horario::where('profesor_id', $duplicado->id)->update(['profesor_id' => $principal->id]);
                    Grupo::where('profesor_id', $duplicado->id)->update(['profesor_id' => $principal->id]);

                    if (empty($principal->user_id) && !empty($duplicado->user_id)) {
                        $principal->user()->associate($duplicado->user_id);
                        $principal->save();
                    }

                    $duplicado->delete();
                    $eliminados++;
                });
            }
        }

        $this->profesoresPorCanon = [];

        return $eliminados;
    }

    /**
     * Normalizar nombre para presentacion y deduplicacion.
     */
    private function normalizarNombreProfesor(string $nombre): string
    {
        $nombre = mb_strtoupper(trim($nombre), 'UTF-8');
        $nombre = preg_replace('/\s+/u', ' ', $nombre);
        $nombre = preg_replace('/\s*-\s*/u', ' - ', $nombre);

        // Corrige fragmentaciones tipo "A RCHUNDIA", "ROMER O", "ZENT ENO".
        do {
            $anterior = $nombre;
            $nombre = preg_replace('/\b([A-Z])\s+([A-Z]{2,})\b/u', '$1$2', $nombre);
            $nombre = preg_replace('/\b([A-Z]{2,})\s+([A-Z])\b/u', '$1$2', $nombre);
            $nombre = preg_replace('/\s+/u', ' ', $nombre);
        } while ($nombre !== $anterior);

        return trim($nombre);
    }

    /**
     * Generar clave canonica para comparar variantes de un mismo profesor.
     */
    private function nombreCanonicoProfesor(string $nombre): string
    {
        $normalizado = $this->normalizarNombreProfesor($nombre);
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalizado);
        if ($ascii === false) {
            return '';
        }

        $ascii = strtoupper($ascii);
        return preg_replace('/[^A-Z]/', '', $ascii) ?? '';
    }

    /**
     * Menor puntaje = mejor calidad de nombre (menos fragmentado).
     */
    private function puntajeNombreProfesor(string $nombre): int
    {
        $partes = preg_split('/\s+/u', trim($nombre)) ?: [];
        $tokensUnaLetra = 0;

        foreach ($partes as $parte) {
            if (preg_match('/^[A-Z]$/u', $parte)) {
                $tokensUnaLetra++;
            }
        }

        return $tokensUnaLetra;
    }

    /**
     * Vincular profesor con su grupo (equipo).
     */
    private function asignarProfesorAGrupo(Grupo $grupo, Profesor $profesor): string
    {
        if (empty($grupo->profesor_id)) {
            $grupo->profesor()->associate($profesor);
            $grupo->save();
            return 'asignado';
        }

        if ((int) $grupo->profesor_id === (int) $profesor->id) {
            return 'ya_asignado';
        }

        return 'conflicto';
    }

    /**
     * Crear cuenta de usuario para el profesor si no existe.
     */
    private function sincronizarCuentaProfesor(Profesor $profesor, array &$resultado): void
    {
        $usuario = User::where('matricula', $profesor->matricula)->first();
        if (!$usuario) {
            $passwordPlano = $this->generarPasswordInicial($profesor->matricula);

            $usuario = User::create([
                'name' => $profesor->nombre,
                'matricula' => $profesor->matricula,
                'password' => Hash::make($passwordPlano),
                'role' => 'profesor',
            ]);

            $resultado['usuarios_profesor_creados']++;
            $resultado['credenciales_nuevas'][] = [
                'nombre' => $profesor->nombre,
                'matricula' => $profesor->matricula,
                'password' => $passwordPlano,
            ];
        } elseif ($usuario->role !== 'profesor') {
            $usuario->update(['role' => 'profesor']);
        }

        if ((int) ($profesor->user_id ?? 0) !== (int) $usuario->id) {
            $profesor->user()->associate($usuario);
            $profesor->save();
        }
    }

    /**
     * Password inicial deterministico para docentes nuevos.
     */
    private function generarPasswordInicial(string $matricula): string
    {
        return 'Docente#' . substr(strtoupper(md5($matricula)), 0, 6);
    }

    /**
     * Eliminar un horario.
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Acceso denegado. Solo los administradores pueden eliminar horarios.');
        }

        $horario = Horario::findOrFail($id);
        $horario->delete();

        return back()->with('success', 'Horario eliminado correctamente');
    }
}
