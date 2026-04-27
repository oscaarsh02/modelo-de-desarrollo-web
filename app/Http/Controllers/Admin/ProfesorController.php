<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profesor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProfesorController extends Controller
{

    public function index()
    {
        $profesores = Profesor::with('user')->orderBy('nombre')->get();
        return view('admin.profesores.index', compact('profesores'));
    }

    public function search(Request $request)
    {
        $query = $request->input('buscar', '');
        $profesores = [];

        if ($query) {
            $profesores = Profesor::with('user')
                ->where('nombre', 'like', "%{$query}%")
                ->orWhere('matricula', 'like', "%{$query}%")
                ->orderBy('nombre')
                ->get();
        }

        return view('admin.profesores.search', compact('profesores', 'query'));
    }

    public function create()
    {
        return view('admin.profesores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'matricula' => ['required', 'string', 'max:255', 'unique:profesors,matricula'],
        ]);

        $profesor = Profesor::create([
            'nombre' => $request->nombre,
            'matricula' => $request->matricula,
        ]);

        $passwordPlano = $this->generarPasswordAdmin();
        $user = User::updateOrCreate(
            ['matricula' => $profesor->matricula],
            [
                'name' => $profesor->nombre,
                'password' => Hash::make($passwordPlano),
                'role' => 'profesor',
            ]
        );

        $profesor->user()->associate($user);
        $profesor->save();

        return redirect()
            ->route('profesores.index')
            ->with('success', "Profesor creado correctamente.\nMatricula: {$profesor->matricula}\nPassword temporal: {$passwordPlano}");
    }

    public function resetPassword(Profesor $profesor)
    {
        $passwordPlano = $this->generarPasswordAdmin();

        $user = User::updateOrCreate(
            ['matricula' => $profesor->matricula],
            [
                'name' => $profesor->nombre,
                'password' => Hash::make($passwordPlano),
                'role' => 'profesor',
            ]
        );

        if ((int) ($profesor->user_id ?? 0) !== (int) $user->id) {
            $profesor->user()->associate($user);
            $profesor->save();
        }

        return redirect()
            ->route('profesores.index')
            ->with('success', "Password regenerado.\nProfesor: {$profesor->nombre}\nMatricula: {$profesor->matricula}\nNuevo password: {$passwordPlano}");
    }

    private function generarPasswordAdmin(): string
    {
        return 'Docente#' . strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6));
    }
}
