<?php

namespace App\Http\Controllers;

use App\Models\Profesor;
use Illuminate\Http\Request;

class ProfesorGruposController extends Controller
{
    /**
     * Mostrar grupos/equipos asignados al profesor autenticado.
     */
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'profesor') {
            abort(403, 'Acceso denegado. Solo profesores.');
        }

        $usuario = auth()->user();

        $profesor = Profesor::where('user_id', $usuario->id)
            ->orWhere('matricula', $usuario->matricula)
            ->first();

        if (!$profesor) {
            return view('profesores.mis_grupos', [
                'profesor' => null,
                'grupos' => collect(),
            ]);
        }

        $grupos = $profesor->grupos()
            ->with([
                'materia',
                'horarios' => function ($query) {
                    $query->with('salon')->orderBy('dia')->orderBy('hora');
                },
            ])
            ->withCount('alumnos')
            ->orderBy('nombre')
            ->get();

        return view('profesores.mis_grupos', compact('profesor', 'grupos'));
    }
}
