@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-4">
            <a href="{{ route('grupos.show', $grupo) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al grupo
            </a>
            <span class="eyebrow">Calificar actividad</span>
            <h1 class="text-4xl font-bold tracking-tight">{{ $actividad->nombre }}</h1>
            <div class="flex flex-wrap gap-2">
                <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">{{ $grupo->materia->clave ?? '' }}</span>
                <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">{{ $actividad->categoria->nombre ?? '' }}</span>
                <span class="rounded-full border border-slate-200 bg-white/80 px-3 py-1 text-xs font-semibold text-slate-600">{{ $alumnos->count() }} alumno(s)</span>
            </div>
        </div>
    </section>

    {{-- Alertas --}}
    @if(session('success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-6 py-4">
            <p class="text-sm font-semibold text-amber-800">Advertencias al importar:</p>
            <ul class="mt-1 list-inside list-disc space-y-1 text-xs text-amber-700">
                @foreach(session('import_errors') as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2" x-data="{ modo: 'directo' }">

        {{-- Calificacion directa --}}
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200/80 px-6 py-5">
                <h2 class="text-xl font-bold">Calificacion directa</h2>
                <p class="mt-0.5 text-xs text-slate-500">Ingresa la calificacion de cada alumno (0–100).</p>
            </div>

            @if($alumnos->isEmpty())
                <p class="px-6 py-8 text-sm text-slate-400 text-center">No hay alumnos activos en este grupo.</p>
            @else
                <form method="POST" action="{{ route('actividades.calificar.directa', $actividad) }}">
                    @csrf
                    <div class="divide-y divide-slate-100 max-h-[480px] overflow-y-auto">
                        @foreach($alumnos as $alumno)
                            <div class="flex items-center justify-between gap-4 px-6 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $alumno->nombre }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ $alumno->matricula }}</p>
                                </div>
                                <input
                                    type="number"
                                    name="calificaciones[{{ $alumno->id }}]"
                                    value="{{ $calificaciones[$alumno->id] ?? '' }}"
                                    min="0" max="100" step="0.01"
                                    placeholder="–"
                                    class="w-24 rounded-2xl border border-slate-200 bg-white/70 px-3 py-2 text-center text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-orange-400">
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-slate-200/80 px-6 py-4">
                        <button type="submit" class="btn-primary w-full justify-center">Guardar calificaciones</button>
                    </div>
                </form>
            @endif
        </section>

        {{-- Subir Excel --}}
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200/80 px-6 py-5">
                <h2 class="text-xl font-bold">Importar desde Excel</h2>
                <p class="mt-0.5 text-xs text-slate-500">El archivo debe tener dos columnas: <strong>Matricula</strong> y <strong>Calificacion</strong>.</p>
            </div>
            <div class="px-6 py-6 space-y-5">

                <div class="rounded-[22px] border border-slate-200 bg-slate-50/70 p-4 space-y-2">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Formato esperado</p>
                    <table class="text-xs w-full">
                        <thead><tr class="text-slate-400"><th class="text-left pb-1">Matricula</th><th class="text-left pb-1">Calificacion</th></tr></thead>
                        <tbody class="font-mono text-slate-700">
                            <tr><td>202132799</td><td>87.5</td></tr>
                            <tr><td>202132800</td><td>92</td></tr>
                        </tbody>
                    </table>
                </div>

                <form method="POST" action="{{ route('actividades.calificar.excel', $actividad) }}" enctype="multipart/form-data" class="space-y-4"
                      x-data="{ nombre: null }">
                    @csrf
                    <label
                        :class="nombre
                            ? 'border-emerald-400 bg-emerald-50 hover:bg-emerald-50'
                            : 'border-slate-300 bg-white/60 hover:bg-white/80'"
                        class="flex flex-col items-center justify-center w-full h-32 rounded-[22px] border-2 border-dashed cursor-pointer transition-all duration-200">
                        <template x-if="!nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        </template>
                        <template x-if="nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-7 w-7 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </template>
                        <p class="text-sm" :class="nombre ? 'font-semibold text-emerald-700' : 'text-slate-500'">
                            <template x-if="!nombre"><span><span class="font-semibold text-orange-700">Haz clic</span> o arrastra el archivo</span></template>
                            <template x-if="nombre"><span x-text="nombre"></span></template>
                        </p>
                        <p class="mt-1 text-xs" :class="nombre ? 'text-emerald-500' : 'text-slate-400'">
                            <span x-show="!nombre">XLSX / XLS</span>
                            <span x-show="nombre">Archivo listo para subir</span>
                        </p>
                        <input type="file" name="archivo" accept=".xlsx,.xls" class="hidden"
                               @change="nombre = $event.target.files[0]?.name ?? null">
                    </label>
                    @error('archivo')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    <button type="submit" class="btn-secondary w-full justify-center">Importar calificaciones</button>
                </form>

            </div>
        </section>

    </div>
</div>
@endsection
