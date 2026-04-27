<?php

namespace App\Http\Controllers;

use App\Models\Actividad;
use App\Models\Grupo;
use App\Models\Profesor;
use Illuminate\Http\Request;

class ActividadController extends Controller
{
    public function store(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $data = $request->validate([
            'nombre'       => 'required|string|max:150',
            'categoria_id' => 'required|exists:categorias,id',
        ]);

        $data['grupo_id'] = $grupo->id;
        Actividad::create($data);

        return redirect()->route('grupos.show', $grupo)->with('success', 'Actividad creada.');
    }

    public function destroy(Actividad $actividad)
    {
        $grupo = $actividad->grupo;
        $this->authorizeProfesor($grupo);

        $actividad->delete();

        return redirect()->route('grupos.show', $grupo)->with('success', 'Actividad eliminada.');
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
