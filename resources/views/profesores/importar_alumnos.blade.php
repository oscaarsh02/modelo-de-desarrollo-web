@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10">
            <a href="{{ route('grupos.show', $grupo) }}" class="mb-6 inline-flex items-center gap-2 text-sm font-semibold text-slate-500 transition hover:text-slate-900">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Volver al grupo
            </a>
            <div class="space-y-3">
                <span class="eyebrow">Gestión de lista</span>
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
                        {{ $grupo->alumnosActivos()->count() }} alumno(s) activo(s)
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Alertas --}}
    @if(session('import_success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-6 py-5 text-emerald-900">
            <p class="font-semibold">{{ session('import_success') }}</p>
        </div>
    @endif
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-5 text-red-900">
            <p class="font-semibold">Algunos registros no se pudieron importar:</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-sm">
                @foreach(session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-5 text-red-900 font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">

        {{-- 1ª Pasada: Alta de alumnos --}}
        <section class="panel p-8 space-y-6">
            <div>
                <span class="inline-block rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-widest text-orange-800 mb-2">1ª Pasada</span>
                <h2 class="text-2xl font-bold">Alta de alumnos</h2>
                <p class="mt-1 text-sm leading-7 text-slate-500">
                    Sube el archivo .html de autoservicios BUAP para registrar a los alumnos del grupo. El sistema extrae nombre, matrícula y correo automáticamente.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('grupos.importar.store', $grupo) }}"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf

                <div x-data="{ nombre: null }">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Archivo HTM de lista de clase
                    </label>
                    <label
                        :class="nombre ? 'border-emerald-400 bg-emerald-50' : 'border-slate-300 bg-white/60 hover:bg-white/80'"
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
                        <p class="mt-1 text-xs text-slate-400" x-show="!nombre">Archivo .htm del SAES BUAP</p>
                        <input type="file" name="archivo" accept=".htm,.html" class="hidden"
                               @change="nombre = $event.target.files[0]?.name ?? null" />
                    </label>
                    @error('archivo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary w-full justify-center">
                    Importar lista (1ª pasada)
                </button>
            </form>
        </section>

        {{-- 2ª Pasada: Conciliación --}}
        <section class="panel p-8 space-y-6">
            <div>
                <span class="inline-block rounded-full bg-blue-100 px-3 py-1 text-xs font-bold uppercase tracking-widest text-blue-800 mb-2">2ª Pasada</span>
                <h2 class="text-2xl font-bold">Conciliación de lista</h2>
                <p class="mt-1 text-sm leading-7 text-slate-500">
                    Sube nuevamente el archivo HTM para detectar <strong>alumnos nuevos, bajas</strong> y diferencias respecto a la lista registrada.
                    El sistema te mostrará los cambios para que los confirmes.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('grupos.conciliar.preview', $grupo) }}"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf

                <div x-data="{ nombre: null }">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Archivo HTM actualizado
                    </label>
                    <label
                        :class="nombre ? 'border-blue-400 bg-blue-50' : 'border-slate-300 bg-white/60 hover:bg-white/80'"
                        class="flex flex-col items-center justify-center w-full h-36 rounded-[22px] border-2 border-dashed cursor-pointer transition-all duration-200">
                        <template x-if="!nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </template>
                        <template x-if="nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-8 w-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <p class="text-sm" :class="nombre ? 'text-blue-700 font-semibold' : 'text-slate-500'">
                            <template x-if="!nombre"><span><span class="font-semibold text-blue-700">Haz clic</span> o arrastra el archivo aquí</span></template>
                            <template x-if="nombre"><span x-text="nombre"></span></template>
                        </p>
                        <p class="mt-1 text-xs text-slate-400" x-show="!nombre">Archivo .htm del SAES BUAP</p>
                        <input type="file" name="archivo" accept=".htm,.html" class="hidden"
                               @change="nombre = $event.target.files[0]?.name ?? null" />
                    </label>
                </div>

                <button type="submit" class="w-full justify-center rounded-full border border-blue-300 bg-blue-50 px-6 py-3 text-sm font-semibold text-blue-800 transition hover:bg-blue-100">
                    Ver diferencias (2ª pasada)
                </button>
            </form>
        </section>

    </div>

    {{-- Lista de alumnos actuales --}}
    <section class="panel overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Alumnos registrados</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $grupo->alumnosActivos()->count() }} activo(s) ·
                    {{ $grupo->alumnos()->wherePivotNotNull('baja_at')->count() }} baja(s)
                </p>
            </div>
        </div>

        @php
            $todosAlumnos = $grupo->alumnos()->withPivot('baja_at')->orderBy('nombre')->get();
        @endphp

        @if($todosAlumnos->isEmpty())
            <div class="px-6 py-10 text-center">
                <p class="text-sm text-slate-400">Aún no hay alumnos. Importa el archivo HTM para comenzar.</p>
            </div>
        @else
            <div class="divide-y divide-slate-100 max-h-[400px] overflow-y-auto">
                @foreach($todosAlumnos as $alumno)
                    @php $esBaja = $alumno->pivot->baja_at !== null; @endphp
                    <div class="flex items-center justify-between gap-4 px-6 py-3 {{ $esBaja ? 'opacity-50' : '' }}">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $alumno->nombre }}</p>
                            <p class="font-mono text-xs text-slate-400">{{ $alumno->matricula }}</p>
                            @if($alumno->correo)
                                <p class="text-xs text-slate-400">{{ $alumno->correo }}</p>
                            @endif
                        </div>
                        @if($esBaja)
                            <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Baja</span>
                        @else
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Activo</span>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

</div>
@endsection
