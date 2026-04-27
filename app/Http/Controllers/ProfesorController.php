<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ProfesorController extends Controller
{

    public function index()
    {
        $profesores = User::where('role','profesor')->get();
        return view('admin.profesores.index', compact('profesores'));
    }

    public function create()
    {
        return view('admin.profesores.create');
    }

    public function store(Request $request)
    {
        $password = Str::random(8);

        User::create([
            'name' => $request->name,
            'matricula' => $request->matricula,
            'password' => Hash::make($password),
            'role' => 'profesor'
        ]);

        return redirect('/admin/profesores');
    }
}