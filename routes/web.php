<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProfesorController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\HorariosController;
use App\Http\Controllers\ProfesorGruposController;
use App\Http\Controllers\AlumnoImportController;
use App\Http\Controllers\GrupoDetalleController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\CalificacionController;
use App\Http\Controllers\TeamsImportController;
use App\Http\Controllers\ExportCalificacionesController;
use App\Http\Controllers\ProfileController;
use App\Models\Alumno;
use App\Models\Grupo;
use App\Models\Horario;
use App\Models\Profesor;

Route::get('/subir-pdf', [PdfController::class, 'form']);
Route::post('/procesar-pdf', [PdfController::class, 'procesar']);

// Rutas para horarios - todos los usuarios autenticados pueden ver
Route::middleware('auth')->group(function () {
    Route::get('/horarios', [HorariosController::class, 'index'])->name('horarios.index');
    Route::get('/horarios/create', [HorariosController::class, 'create'])->name('horarios.create');
    Route::post('/horarios', [HorariosController::class, 'store'])->name('horarios.store');
    Route::delete('/horarios/{horario}', [HorariosController::class, 'destroy'])->name('horarios.destroy');
    Route::get('/mis-grupos', [ProfesorGruposController::class, 'index'])->name('profesor.grupos');

    // Lista HTM — 1ª y 2ª pasada
    Route::get('/grupos/{grupo}/importar-alumnos', [AlumnoImportController::class, 'showForm'])->name('grupos.importar.form');
    Route::post('/grupos/{grupo}/importar-alumnos', [AlumnoImportController::class, 'import'])->name('grupos.importar.store');
    Route::post('/grupos/{grupo}/conciliar-preview', [AlumnoImportController::class, 'previewConciliacion'])->name('grupos.conciliar.preview');
    Route::post('/grupos/{grupo}/conciliar-aplicar', [AlumnoImportController::class, 'aplicarConciliacion'])->name('grupos.conciliar.aplicar');

    // Calificaciones de Teams
    Route::get('/grupos/{grupo}/importar-teams', [TeamsImportController::class, 'showForm'])->name('grupos.teams.form');
    Route::post('/grupos/{grupo}/importar-teams/preview', [TeamsImportController::class, 'preview'])->name('grupos.teams.preview');
    Route::post('/grupos/{grupo}/importar-teams/store', [TeamsImportController::class, 'store'])->name('grupos.teams.store');

    // Exportar calificaciones
    Route::get('/grupos/{grupo}/exportar', [ExportCalificacionesController::class, 'preview'])->name('grupos.exportar');
    Route::post('/grupos/{grupo}/exportar/guardar', [ExportCalificacionesController::class, 'guardarAjustes'])->name('grupos.exportar.guardar');
    Route::get('/grupos/{grupo}/exportar/download', [ExportCalificacionesController::class, 'download'])->name('grupos.exportar.download');

    // Detalle del grupo (profesor)
    Route::get('/grupos/{grupo}/detalle', [GrupoDetalleController::class, 'show'])->name('grupos.show');

    // Categorias
    Route::post('/grupos/{grupo}/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    // Actividades
    Route::post('/grupos/{grupo}/actividades', [ActividadController::class, 'store'])->name('actividades.store');
    Route::delete('/actividades/{actividad}', [ActividadController::class, 'destroy'])->name('actividades.destroy');

    // Calificar
    Route::get('/actividades/{actividad}/calificar', [CalificacionController::class, 'form'])->name('actividades.calificar');
    Route::post('/actividades/{actividad}/calificar/directa', [CalificacionController::class, 'storeDirecto'])->name('actividades.calificar.directa');
    Route::post('/actividades/{actividad}/calificar/excel', [CalificacionController::class, 'storeExcel'])->name('actividades.calificar.excel');

    // Asistencia
    Route::get('/grupos/{grupo}/asistencia', [AsistenciaController::class, 'scanner'])->name('asistencia.scanner');
    Route::post('/asistencia', [AsistenciaController::class, 'store'])->name('asistencia.store');
    Route::get('/grupos/{grupo}/asistencia/manual', [AsistenciaController::class, 'manual'])->name('asistencia.manual');
    Route::post('/grupos/{grupo}/asistencia/manual', [AsistenciaController::class, 'storeManual'])->name('asistencia.manual.store');

    // Alumno
    Route::get('/alumno/dashboard', [AlumnoImportController::class, 'dashboard'])->name('alumno.dashboard');
    Route::get('/alumno/qr', [AsistenciaController::class, 'qr'])->name('alumno.qr');
    Route::get('/alumno/asistencias', [AsistenciaController::class, 'historial'])->name('alumno.asistencias');
    Route::post('/grupos/{grupo}/baja', [AlumnoImportController::class, 'baja'])->name('alumno.baja');
    Route::post('/grupos/{grupo}/alumnos/{alumno}/reinscribir', [AlumnoImportController::class, 'reinscribir'])->name('alumno.reinscribir');
});

Route::middleware(['auth','admin'])->group(function(){

    Route::get('/admin/profesores', [ProfesorController::class,'index'])->name('profesores.index');
    Route::get('/admin/profesores/buscar', [ProfesorController::class,'search'])->name('profesores.search');
    Route::get('/admin/profesores/crear', [ProfesorController::class,'create'])->name('profesores.create');
    Route::post('/admin/profesores', [ProfesorController::class,'store'])->name('profesores.store');
    Route::post('/admin/profesores/{profesor}/reset-password', [ProfesorController::class, 'resetPassword'])->name('profesores.reset-password');

});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (auth()->user()->role === 'profesor') {
        return redirect()->route('profesor.grupos');
    }

    if (auth()->user()->role === 'alumno') {
        return redirect()->route('alumno.dashboard');
    }

    return view('dashboard', [
        'stats' => [
            'profesores' => Profesor::count(),
            'equipos' => Grupo::count(),
            'alumnos' => Alumno::count(),
            'bloques' => Horario::count(),
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
