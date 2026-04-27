@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10">
            <a href="{{ route('profesor.grupos') }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver a mis grupos
            </a>

            <div class="space-y-3">
                <span class="eyebrow">Importar alumnos</span>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                    {{ $grupo->materia->nombre ?? 'Grupo' }}
                </h1>
                <div class="flex flex-wrap gap-2">
                    <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">
                        {{ $grupo->materia->clave ?? 'SIN CLAVE' }}
                    </span>
                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">
                        Equipo {{ $grupo->nombre }}
                    </span>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-teal-800">
                        {{ $grupo->alumnos->count() }} alumno(s) registrado(s)
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Alertas --}}
    @if(session('import_success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-6 py-5 text-emerald-900">
            <p class="font-semibold">{{ session('import_success') }}</p>
            @if(session('import_success') && str_contains(session('import_success'), '0 alumno(s)') === false)
                <p class="mt-1 text-sm">La contrasena inicial de cada alumno es su propia matricula.</p>
            @endif
        </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-5 text-red-900">
            <p class="font-semibold">Algunas filas no se pudieron importar:</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr,1.2fr]">

        {{-- Formulario de carga --}}
        <section class="panel p-8">
            <h2 class="mb-1 text-2xl font-bold">Cargar archivo Excel</h2>
            <p class="mb-6 text-sm leading-7 text-slate-500">
                El archivo debe tener las columnas: <strong>Nombre Completo</strong>, <strong>Matricula</strong> y <strong>Carrera</strong>.
                Se creara una cuenta de acceso para cada alumno nuevo con su matricula como contrasena.
            </p>

            <form method="POST"
                  action="{{ route('grupos.importar.store', $grupo) }}"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf

                <div x-data="{ nombre: null }">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Archivo Excel (.xlsx / .xls)
                    </label>
                    <label
                        :class="nombre
                            ? 'border-emerald-400 bg-emerald-50 hover:bg-emerald-50'
                            : 'border-slate-300 bg-white/60 hover:bg-white/80'"
                        class="flex flex-col items-center justify-center w-full h-36 rounded-[22px] border-2 border-dashed cursor-pointer transition-all duration-200">
                        <template x-if="!nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                        </template>
                        <template x-if="nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <p class="text-sm" :class="nombre ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                            <template x-if="!nombre"><span><span class="font-semibold text-orange-700">Haz clic</span> o arrastra el archivo aquí</span></template>
                            <template x-if="nombre"><span x-text="nombre"></span></template>
                        </p>
                        <p class="mt-1 text-xs" :class="nombre ? 'text-emerald-500' : 'text-slate-400'">
                            <span x-show="!nombre">XLSX, XLS permitidos</span>
                            <span x-show="nombre">Archivo listo para subir</span>
                        </p>
                        <input type="file" name="archivo" accept=".xlsx,.xls" class="hidden"
                               @change="nombre = $event.target.files[0]?.name ?? null" />
                    </label>
                    @error('archivo')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full justify-center">
                    Importar alumnos
                </button>
            </form>
        </section>

        {{-- Lista de alumnos actuales --}}
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200/80 px-6 py-5">
                <h2 class="text-2xl font-bold">Alumnos en el grupo</h2>
                <p class="mt-1 text-sm text-slate-500">{{ $grupo->alumnos->count() }} registrado(s) actualmente.</p>
            </div>

            @if($grupo->alumnos->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-400">Aun no hay alumnos en este grupo.</p>
                    <p class="mt-1 text-sm text-slate-400">Carga un archivo Excel para comenzar.</p>
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($grupo->alumnos as $alumno)
                        <div class="flex items-center justify-between gap-4 px-6 py-4">
                            <div>
                                <p class="text-sm font-semibold text-slate-900">{{ $alumno->nombre }}</p>
                                <p class="font-mono text-xs text-slate-500">{{ $alumno->matricula }}</p>
                            </div>
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                Alumno
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
