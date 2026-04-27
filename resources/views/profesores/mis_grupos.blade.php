@extends('layouts.app')

@section('content')
<div class="shell space-y-8">
    <section class="panel-strong overflow-hidden">
        <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.3fr,0.9fr] lg:px-10 lg:py-10">
            <div class="space-y-5">
                <span class="eyebrow">Vista docente</span>
                <div class="space-y-4">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                        Consulta tus equipos y horarios en una sola vista.
                    </h1>
                    <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        Esta pantalla muestra lo que hoy ya funciona para el profesor: materias asignadas, grupo, NRC, salon y bloques de clase del periodo actual.
                    </p>
                </div>

                @if($profesor)
                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white">
                            {{ $profesor->nombre }}
                        </div>
                        <div class="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700">
                            {{ $grupos->count() }} equipo(s) asignado(s)
                        </div>
                    </div>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="metric-card">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Equipos</p>
                    <p class="mt-3 text-4xl font-bold text-slate-900">{{ $grupos->count() }}</p>
                    <p class="mt-2 text-sm text-slate-600">Grupos vinculados a tu cuenta.</p>
                </div>
                <div class="metric-card">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Alumnos</p>
                    <p class="mt-3 text-4xl font-bold text-slate-900">{{ $grupos->sum('alumnos_count') }}</p>
                    <p class="mt-2 text-sm text-slate-600">Relacionados actualmente a tus grupos.</p>
                </div>
                <div class="metric-card sm:col-span-2">
                    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Modulo visible</p>
                    <p class="mt-3 text-lg font-bold text-slate-900">Consulta de materias, grupos y horarios</p>
                    <p class="mt-2 text-sm leading-7 text-slate-600">La demo docente se centra en mostrar asignacion academica de forma ordenada.</p>
                </div>
            </div>
        </div>
    </section>

    @if (!$profesor)
        <section class="panel p-8">
            <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <h2 class="text-2xl font-bold">Usuario sin vinculacion docente</h2>
                <p class="mt-3 text-sm leading-7">
                    Tu cuenta aun no esta asociada a un profesor. El administrador debe importar el PDF oficial para enlazar horarios y equipos contigo.
                </p>
            </div>
        </section>
    @elseif($grupos->isEmpty())
        <section class="panel p-8">
            <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <h2 class="text-2xl font-bold">Sin equipos asignados</h2>
                <p class="mt-3 text-sm leading-7">
                    Todavia no hay grupos cargados para este periodo.
                </p>
            </div>
        </section>
    @else
        <section class="grid gap-6 xl:grid-cols-2">
            @foreach($grupos as $grupo)
                @php
                    $ordenDias = ['L' => 1, 'A' => 2, 'M' => 3, 'J' => 4, 'V' => 5, 'S' => 6];
                    $horariosOrdenados = $grupo->horarios->sortBy(function ($item) use ($ordenDias) {
                        $ordenDia = $ordenDias[$item->dia] ?? 99;
                        return sprintf('%02d-%s', $ordenDia, $item->hora);
                    });
                    $nrc = $horariosOrdenados->first()?->nrc;
                @endphp

                <article class="panel overflow-hidden">
                    <div class="border-b border-slate-200/80 px-6 py-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">
                                        {{ $grupo->materia->clave ?? 'SIN CLAVE' }}
                                    </span>
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">
                                        Equipo {{ $grupo->nombre }}
                                    </span>
                                    @if($nrc)
                                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-teal-800">
                                            NRC {{ $nrc }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold">{{ $grupo->materia->nombre ?? 'Materia sin nombre' }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">Informacion academica del equipo asignado.</p>
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-3">
                                <div class="rounded-[22px] border border-slate-200 bg-white/80 px-4 py-3 text-right">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Alumnos</p>
                                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $grupo->alumnos_count }}</p>
                                </div>
                                <a href="{{ route('grupos.show', $grupo) }}" class="btn-primary px-4 py-2 text-sm">
                                    Ver detalle
                                </a>
                                <a href="{{ route('grupos.importar.form', $grupo) }}" class="btn-secondary px-4 py-2 text-sm">
                                    Cargar alumnos
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-lg font-bold">Horario del equipo</h3>
                            <span class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">{{ $horariosOrdenados->count() }} bloque(s)</span>
                        </div>

                        <div class="space-y-3">
                            @foreach($horariosOrdenados as $horario)
                                <div class="rounded-[22px] border border-slate-200 bg-white/85 p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div class="flex items-center gap-3">
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-700">
                                                {{ $horario->dia }}
                                            </span>
                                            <span class="font-mono text-sm font-semibold text-slate-800">{{ $horario->hora }}</span>
                                        </div>
                                        <span class="rounded-full bg-orange-50 px-3 py-1 text-xs font-semibold text-orange-800">
                                            {{ $horario->salon->nombre ?? 'Sin salon' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </section>
    @endif
</div>
@endsection
