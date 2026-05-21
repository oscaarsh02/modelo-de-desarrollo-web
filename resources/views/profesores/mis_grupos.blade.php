@extends('layouts.app')

@section('content')
<div class="shell space-y-8">
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
        <section class="grid gap-6 {{ $grupos->count() === 1 ? 'xl:grid-cols-1 xl:justify-items-center' : 'xl:grid-cols-2' }}">
            @foreach($grupos as $grupo)
                @php
                    $ordenDias = ['L' => 1, 'A' => 2, 'M' => 3, 'J' => 4, 'V' => 5, 'S' => 6];
                    $horariosOrdenados = $grupo->horarios->sortBy(function ($item) use ($ordenDias) {
                        $ordenDia = $ordenDias[$item->dia] ?? 99;
                        return sprintf('%02d-%s', $ordenDia, $item->hora);
                    });
                    $nrc = $horariosOrdenados->first()?->nrc;
                @endphp

                <article class="panel w-full overflow-hidden {{ $grupos->count() === 1 ? 'max-w-4xl' : '' }}">
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
                                    @if($grupo->profesor)
                                        <p class="mt-1 text-sm text-slate-500">Docente: <span class="font-semibold">{{ $grupo->profesor->nombre }}</span></p>
                                    @endif
                                </div>
                            </div>

                            <div class="flex flex-col items-end gap-3">
                                <div class="rounded-[22px] border border-slate-200 bg-white/80 px-4 py-3 text-right">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Alumnos</p>
                                    <p class="mt-1 text-3xl font-bold text-slate-900">{{ $grupo->alumnos_count }}</p>
                                </div>
                                <a href="{{ route('grupos.show', $grupo) }}" class="btn-primary px-4 py-2 text-sm">
                                    Ver grupo
                                </a>
                                <a href="{{ route('asistencia.scanner', $grupo) }}"
                                   class="inline-flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold text-white transition"
                                   style="background:linear-gradient(135deg,#0f766e 0%,#14b8a6 100%);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4m10-16h-4a2 2 0 00-2 2v4m6 10h-4a2 2 0 01-2-2v-4"/></svg>
                                    Asistencia
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-6">
                        <div class="mb-4">
                            <h3 class="text-lg font-bold">Horario del equipo</h3>
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
