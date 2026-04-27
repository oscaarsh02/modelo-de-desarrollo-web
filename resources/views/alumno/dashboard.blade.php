@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10">
            <div class="space-y-5">
                <span class="eyebrow">Vista alumno</span>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                    {{ $alumno ? 'Hola, ' . explode(' ', $alumno->nombre)[0] . '.' : 'Bienvenido.' }}
                </h1>
                <p class="max-w-2xl text-base leading-8 text-slate-600">
                    Consulta tus materias, calificaciones y promedio ponderado en tiempo real.
                </p>
                @if($alumno)
                    <div class="flex flex-wrap gap-3">
                        <div class="rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white">{{ $alumno->nombre }}</div>
                        <div class="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700">Matricula: {{ $alumno->matricula }}</div>
                        <div class="rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-sm font-semibold text-slate-700">{{ $gruposConConcentrado->count() }} materia(s)</div>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- Alertas --}}
    @if(session('baja_success'))
        <div class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-4 text-sm font-semibold text-red-800">{{ session('baja_success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-[24px] border border-amber-200 bg-amber-50 px-6 py-4 text-sm font-semibold text-amber-800">{{ session('error') }}</div>
    @endif

    {{-- Sin vinculacion --}}
    @if(!$alumno)
        <section class="panel p-8">
            <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <h2 class="text-2xl font-bold">Cuenta sin vinculacion</h2>
                <p class="mt-3 text-sm leading-7">Tu cuenta no esta asociada a un alumno. Contacta a tu docente para que te registre en tu grupo.</p>
            </div>
        </section>
    @elseif($gruposConConcentrado->isEmpty())
        <section class="panel p-8">
            <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <h2 class="text-2xl font-bold">Sin materias inscritas</h2>
                <p class="mt-3 text-sm leading-7">Todavia no estas registrado en ningun grupo activo.</p>
            </div>
        </section>
    @else
        <div class="space-y-8">
            @foreach($gruposConConcentrado as $item)
                @php
                    $grupo     = $item['grupo'];
                    $filaCats  = $item['categorias'];
                    $promFinal = $item['promedio_ponderado'];
                    $ordenDias = ['L'=>1,'A'=>2,'M'=>3,'J'=>4,'V'=>5,'S'=>6];
                    $horarios  = $grupo->horarios->sortBy(fn($h) => sprintf('%02d', $ordenDias[$h->dia] ?? 99));
                @endphp

                <article class="panel overflow-hidden" x-data="{ confirmarBaja: false }">

                    {{-- Cabecera de materia --}}
                    <div class="border-b border-slate-200/80 px-6 py-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-2">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">{{ $grupo->materia->clave ?? 'SIN CLAVE' }}</span>
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">Equipo {{ $grupo->nombre }}</span>
                                </div>
                                <h2 class="text-2xl font-bold">{{ $grupo->materia->nombre ?? 'Materia sin nombre' }}</h2>
                                @if($grupo->profesor)
                                    <p class="text-sm text-slate-500">Docente: <span class="font-semibold text-slate-700">{{ $grupo->profesor->nombre }}</span></p>
                                @endif

                                {{-- Horario compacto --}}
                                @if($horarios->isNotEmpty())
                                    <div class="flex flex-wrap gap-2 pt-1">
                                        @foreach($horarios as $h)
                                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                                {{ $h->dia }} {{ $h->hora }} · {{ $h->salon->nombre ?? '–' }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Promedio final + acciones --}}
                            <div class="flex flex-col items-end gap-3">
                                <div class="rounded-[22px] border border-slate-200 bg-white/80 px-5 py-4 text-right">
                                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Promedio ponderado</p>
                                    <p class="mt-1 text-4xl font-bold {{ $promFinal !== null ? ($promFinal >= 70 ? 'text-slate-900' : 'text-red-600') : 'text-slate-300' }}">
                                        {{ $promFinal !== null ? number_format($promFinal, 1) : '–' }}
                                    </p>
                                </div>

                                {{-- Mi QR --}}
                                <a href="{{ route('alumno.qr') }}"
                                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 text-xs font-semibold text-white transition"
                                    style="background:linear-gradient(135deg,#0f766e 0%,#14b8a6 100%);">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Mi código QR
                                </a>
                                <a href="{{ route('alumno.asistencias') }}"
                                    class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white/80 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                    Mis asistencias
                                </a>

                                {{-- Baja --}}
                                <button @click="confirmarBaja = true"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-red-200 bg-red-50 px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Dar de baja
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Modal confirmacion baja --}}
                    <div x-show="confirmarBaja" x-cloak
                         class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
                         @keydown.escape.window="confirmarBaja = false">
                        <div class="panel mx-4 max-w-md w-full p-8 space-y-5" @click.stop>
                            <h3 class="text-2xl font-bold text-red-700">Dar de baja en esta materia</h3>
                            <p class="text-sm leading-7 text-slate-600">
                                Estas a punto de darte de baja en <strong>{{ $grupo->materia->nombre ?? 'esta materia' }}</strong>.
                                Esta decision es <strong>permanente e irreversible</strong>. No podras volver a acceder a ella.
                            </p>
                            <div class="flex gap-3">
                                <form method="POST" action="{{ route('alumno.baja', $grupo) }}" class="flex-1">
                                    @csrf
                                    <button type="submit" class="w-full rounded-full bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700 transition">
                                        Confirmar baja permanente
                                    </button>
                                </form>
                                <button @click="confirmarBaja = false" class="btn-secondary flex-1 justify-center">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Concentrado de calificaciones --}}
                    <div class="px-6 py-6">
                        <h3 class="mb-4 text-lg font-bold">Mis calificaciones</h3>

                        @if($filaCats->isEmpty())
                            <p class="text-sm text-slate-400">El docente aun no ha configurado categorias ni actividades.</p>
                        @else
                            <div class="space-y-4">
                                @foreach($filaCats as $filaCat)
                                    <div class="rounded-[22px] border border-slate-200 bg-white/70 overflow-hidden">
                                        <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100 bg-orange-50/60">
                                            <p class="text-sm font-bold text-orange-800">{{ $filaCat['categoria']->nombre }}</p>
                                            <div class="flex items-center gap-3">
                                                <span class="text-xs font-semibold text-slate-500">Ponderacion: {{ $filaCat['categoria']->ponderacion }}%</span>
                                                @if($filaCat['promedio'] !== null)
                                                    <span class="rounded-full px-3 py-1 text-xs font-bold {{ $filaCat['promedio'] >= 70 ? 'bg-teal-100 text-teal-800' : 'bg-red-100 text-red-700' }}">
                                                        Prom: {{ number_format($filaCat['promedio'], 1) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($filaCat['actividades']->isEmpty())
                                            <p class="px-4 py-3 text-xs text-slate-400">Sin actividades en esta categoria.</p>
                                        @else
                                            <div class="divide-y divide-slate-100">
                                                @foreach($filaCat['actividades'] as $actFila)
                                                    <div class="flex items-center justify-between px-4 py-3">
                                                        <p class="text-sm text-slate-700">{{ $actFila['actividad']->nombre }}</p>
                                                        @if($actFila['calificacion'] !== null)
                                                            <span class="font-mono text-sm font-bold {{ $actFila['calificacion'] >= 70 ? 'text-slate-800' : 'text-red-600' }}">
                                                                {{ number_format($actFila['calificacion'], 1) }}
                                                            </span>
                                                        @else
                                                            <span class="text-xs text-slate-300">Sin calificar</span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif

</div>
@endsection
