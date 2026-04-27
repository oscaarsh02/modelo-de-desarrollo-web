<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Grupo;
use App\Models\Profesor;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function store(Request $request, Grupo $grupo)
    {
        $this->authorizeProfesor($grupo);

        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'ponderacion' => 'required|numeric|min:0.01|max:100',
        ]);

        $data['grupo_id'] = $grupo->id;
        Categoria::create($data);

        return redirect()->route('grupos.show', $grupo)->with('success', 'Categoria creada.');
    }

    public function destroy(Categoria $categoria)
    {
        $grupo = $categoria->grupo;
        $this->authorizeProfesor($grupo);

        $categoria->delete();

        return redirect()->route('grupos.show', $grupo)->with('success', 'Categoria eliminada.');
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
