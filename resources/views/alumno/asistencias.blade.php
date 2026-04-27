@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-5">
            <a href="{{ route('alumno.dashboard') }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Mi dashboard
            </a>
            <div class="space-y-3">
                <span class="eyebrow">Mis asistencias</span>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Historial</h1>
                @if($alumno)
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-950 px-4 py-1.5 text-sm font-semibold text-white">{{ $alumno->nombre }}</span>
                        <span class="rounded-full border border-slate-200 bg-white/80 px-4 py-1.5 text-sm font-semibold text-slate-600">{{ $alumno->matricula }}</span>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if(!$alumno)
        <section class="panel p-8">
            <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <h2 class="text-2xl font-bold">Cuenta sin vinculación</h2>
                <p class="mt-3 text-sm leading-7">Tu cuenta no está asociada a un alumno. Contacta a tu docente.</p>
            </div>
        </section>

    @elseif($resumen->isEmpty())
        <section class="panel p-8">
            <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-6 text-slate-600 text-center">
                <p class="text-lg font-bold">Sin materias inscritas</p>
                <p class="mt-2 text-sm">Aún no tienes grupos activos con asistencias registradas.</p>
            </div>
        </section>

    @else
        <div class="space-y-6">
            @foreach($resumen as $item)
                @php $grupo = $item['grupo']; @endphp
                <article class="panel overflow-hidden" x-data="{ expandido: true }">

                    {{-- Cabecera materia --}}
                    <button @click="expandido = !expandido"
                            class="w-full border-b border-slate-200/80 px-6 py-5 text-left transition hover:bg-slate-50/60">
                        <div class="flex items-center justify-between gap-4">
                            <div class="space-y-1.5">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">
                                        {{ $grupo->materia->clave ?? 'SIN CLAVE' }}
                                    </span>
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">
                                        Equipo {{ $grupo->nombre }}
                                    </span>
                                </div>
                                <h2 class="text-xl font-bold">{{ $grupo->materia->nombre ?? 'Materia' }}</h2>
                            </div>

                            <div class="flex items-center gap-4">
                                {{-- Contador total --}}
                                <div class="flex flex-col items-center rounded-2xl bg-teal-50 border border-teal-200 px-4 py-2 min-w-[60px]">
                                    <span class="text-2xl font-black text-teal-700">{{ $item['total'] }}</span>
                                    <span class="text-[10px] font-semibold uppercase tracking-widest text-teal-500">clases</span>
                                </div>
                                {{-- Chevron --}}
                                <svg xmlns="http://www.w3.org/2000/svg"
                                     class="h-5 w-5 text-slate-400 transition-transform duration-200"
                                     :class="expandido ? 'rotate-180' : ''"
                                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                        </div>
                    </button>

                    {{-- Lista de fechas --}}
                    <div x-show="expandido" x-collapse>
                        @if($item['asistencias']->isEmpty())
                            <div class="px-6 py-6 text-center text-sm text-slate-400">
                                Aún no tienes asistencias registradas en esta materia.
                            </div>
                        @else
                            <div class="divide-y divide-slate-100">
                                @foreach($item['asistencias'] as $a)
                                    <div class="flex items-center justify-between px-6 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-8 w-8 flex-shrink-0 rounded-full bg-teal-100 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-800">
                                                    {{ \Carbon\Carbon::parse($a->fecha)->translatedFormat('l, d \d\e F Y') }}
                                                </p>
                                                <p class="text-xs text-slate-400">
                                                    {{ \Carbon\Carbon::parse($a->fecha)->translatedFormat('D') }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="font-mono text-sm font-semibold text-slate-500">
                                            {{ substr($a->hora, 0, 5) }}
                                        </span>
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
