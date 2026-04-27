<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear usuario admin
        User::firstOrCreate(
            ['matricula' => 'ADMIN001'],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123456'),
                'role' => 'admin',
            ]
        );

        // Crear usuario profesor
        User::firstOrCreate(
            ['matricula' => 'PROF001'],
            [
                'name' => 'Profesor Test',
                'password' => Hash::make('profesor123456'),
                'role' => 'profesor',
            ]
        );

        // Crear usuario alumno
        User::firstOrCreate(
            ['matricula' => 'EST001'],
            [
                'name' => 'Alumno Test',
                'password' => Hash::make('alumno123456'),
                'role' => 'alumno',
            ]
        );
    }
}
