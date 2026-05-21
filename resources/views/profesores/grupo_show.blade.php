@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-5">
            <a href="{{ route('profesor.grupos') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Mis grupos
            </a>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-3">
                    <span class="eyebrow">Gestión del grupo</span>
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $grupo->materia->nombre ?? 'Grupo' }}</h1>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">{{ $grupo->materia->clave ?? 'SIN CLAVE' }}</span>
                        <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">Equipo {{ $grupo->nombre }}</span>
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-teal-800">{{ $alumnos->count() }} alumno(s) activo(s)</span>
                    </div>
                </div>

                {{-- Acciones --}}
                <div class="flex flex-wrap gap-2">
                    {{-- Asistencia: acción principal --}}
                    <a href="{{ route('asistencia.scanner', $grupo) }}"
                        class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white transition"
                        style="background: linear-gradient(135deg,#0f766e 0%,#14b8a6 100%); box-shadow:0 8px 24px rgba(15,118,110,0.28);"
                        title="Tomar asistencia escaneando el código QR de cada alumno">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4m10-16h-4a2 2 0 00-2 2v4m6 10h-4a2 2 0 01-2-2v-4"/></svg>
                        Asistencia QR
                    </a>
                    <a href="{{ route('asistencia.manual', $grupo) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
                        title="Marcar asistencias manualmente con lista">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Pase de lista
                    </a>
                    <a href="{{ route('grupos.importar.form', $grupo) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
                        title="Importar la lista de alumnos desde el archivo HTM de Secretaría">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Importar alumnos
                    </a>
                    <a href="{{ route('grupos.teams.form', $grupo) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-purple-200 bg-purple-50 px-5 py-2.5 text-sm font-semibold text-purple-800 hover:bg-purple-100 transition"
                        title="Importar calificaciones desde un archivo Excel de Teams">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Importar de Excel
                    </a>
                    <a href="{{ route('grupos.exportar', $grupo) }}"
                        class="inline-flex items-center gap-2 rounded-full px-5 py-2.5 text-sm font-semibold text-white transition"
                        style="background: linear-gradient(135deg,#16a34a 0%,#22c55e 100%); box-shadow:0 8px 24px rgba(22,163,74,0.20);"
                        title="Descargar el concentrado de calificaciones en formato Excel">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        Exportar Excel
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1fr,1.6fr]">

        {{-- Panel izquierdo: Categorías y Actividades --}}
        <div class="space-y-6">

            {{-- Categorías --}}
            <section class="panel overflow-hidden">
                <div class="border-b border-slate-200/80 px-6 py-5 flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Categorías de calificación</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Ponderación acumulada: {{ $categorias->sum('ponderacion') }}% de 100%</p>
                    </div>
                </div>

                @if($categorias->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($categorias as $cat)
                            <div class="flex items-center justify-between gap-3 px-6 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $cat->nombre }}</p>
                                    <p class="text-xs text-slate-500">{{ $cat->actividades->count() }} actividad(es)</p>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-800">{{ $cat->ponderacion }}%</span>
                                    <form method="POST" action="{{ route('categorias.destroy', $cat) }}"
                                          onsubmit="return confirm('Eliminar esta categoria y todas sus actividades?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-400 hover:text-red-600 transition">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-6 py-4 text-sm text-slate-400">Sin categorías. Agrega una para comenzar.</p>
                @endif

                <form method="POST" action="{{ route('categorias.store', $grupo) }}" class="border-t border-slate-200/80 px-6 py-5 space-y-3">
                    @csrf
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Nueva categoría</p>
                    <div class="flex flex-wrap gap-2">
                        <input type="text" name="nombre" placeholder="Ej: Parcial 1" required
                            class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white/70 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                        <input type="number" name="ponderacion" placeholder="%" min="0.01" max="100" step="0.01" required
                            class="w-20 rounded-2xl border border-slate-200 bg-white/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400"
                            title="Porcentaje de ponderación">
                        <button type="submit" class="btn-primary px-4 py-2 text-sm">Agregar</button>
                    </div>
                    @error('nombre')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                    @error('ponderacion')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                </form>
            </section>

            {{-- Actividades --}}
            <section class="panel overflow-hidden">
                <div class="border-b border-slate-200/80 px-6 py-5">
                    <h2 class="text-xl font-bold">Actividades y tareas</h2>
                    <p class="mt-0.5 text-xs text-slate-500">Cada actividad pertenece a una categoría de calificación.</p>
                </div>

                @if($grupo->actividades->isNotEmpty())
                    <div class="divide-y divide-slate-100">
                        @foreach($grupo->actividades->sortBy('categoria_id') as $act)
                            <div class="flex items-center justify-between gap-3 px-6 py-3">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $act->nombre }}</p>
                                    <p class="text-xs text-slate-500">{{ $act->categoria->nombre ?? '–' }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('actividades.calificar', $act) }}"
                                       class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold text-orange-800 hover:bg-orange-200 transition">
                                        Calificar
                                    </a>
                                    <form method="POST" action="{{ route('actividades.destroy', $act) }}"
                                          onsubmit="return confirm('Eliminar esta actividad y sus calificaciones?')">
                                        @csrf @method('DELETE')
                                        <button class="text-xs font-semibold text-red-400 hover:text-red-600 transition">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="px-6 py-4 text-sm text-slate-400">Aún no hay actividades en este grupo.</p>
                @endif

                @if($categorias->isNotEmpty())
                    <form method="POST" action="{{ route('actividades.store', $grupo) }}" class="border-t border-slate-200/80 px-6 py-5 space-y-3">
                        @csrf
                        <p class="text-xs font-bold uppercase tracking-widest text-slate-500">Nueva actividad</p>
                        <div class="flex flex-wrap gap-2">
                            <input type="text" name="nombre" placeholder="Ej: Tarea 1" required
                                class="min-w-0 flex-1 rounded-2xl border border-slate-200 bg-white/70 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                            <select name="categoria_id" required
                                class="w-full rounded-2xl border border-slate-200 bg-white/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400 sm:w-auto">
                                <option value="" disabled>Seleccionar categoría</option>
                                @foreach($categorias as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-primary px-4 py-2 text-sm">Agregar</button>
                        </div>
                    </form>
                @else
                    <div class="border-t border-slate-200/80 px-6 py-4 bg-amber-50 flex items-start gap-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <p class="text-xs text-amber-700">Primero crea una categoría de calificación para poder agregar actividades.</p>
                    </div>
                @endif
            </section>

        </div>

        {{-- Concentrado --}}
        <section class="panel" style="overflow: hidden;">
            <div class="border-b border-slate-200/80 px-6 py-5">
                <h2 class="text-xl font-bold">Concentrado de calificaciones</h2>
                <p class="mt-0.5 text-xs text-slate-500">{{ $alumnos->count() }} alumno(s) activo(s) · Haz clic en "Calificar" para capturar notas</p>
            </div>

            @if($concentrado->isEmpty() || $categorias->isEmpty())
                <div class="px-6 py-10 text-center">
                    <p class="text-sm text-slate-400">
                        @if($alumnos->isEmpty())
                            No hay alumnos activos en este grupo.
                        @elseif($categorias->isEmpty())
                            Agrega categorias y actividades para ver el concentrado.
                        @else
                            Sin datos todavia.
                        @endif
                    </p>
                </div>
            @else
                <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                    <table class="text-sm" style="min-width: 100%; width: max-content;">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/80">
                                <th class="sticky left-0 z-10 bg-slate-50/90 px-4 py-3 text-left text-xs font-bold uppercase tracking-widest text-slate-500 backdrop-blur">Alumno</th>
                                @foreach($concentrado->first()['categorias'] as $filaCat)
                                    @foreach($filaCat['actividades'] as $actFila)
                                        <th class="px-3 py-3 text-center text-xs font-semibold text-slate-600 whitespace-nowrap">{{ $actFila['actividad']->nombre }}</th>
                                    @endforeach
                                    <th class="px-3 py-3 text-center text-xs font-bold text-orange-700 whitespace-nowrap bg-orange-50/60">
                                        {{ $filaCat['categoria']->nombre }}<br>
                                        <span class="font-normal text-slate-400">({{ $filaCat['categoria']->ponderacion }}%)</span>
                                    </th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-bold uppercase tracking-widest text-slate-700 whitespace-nowrap bg-slate-100/60">Final</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($concentrado as $fila)
                                <tr class="hover:bg-white/60 transition">
                                    <td class="sticky left-0 z-10 bg-white/80 px-4 py-3 backdrop-blur">
                                        <p class="font-semibold text-slate-900">{{ $fila['alumno']->nombre }}</p>
                                        <p class="font-mono text-[11px] text-slate-400">{{ $fila['alumno']->matricula }}</p>
                                    </td>
                                    @foreach($fila['categorias'] as $filaCat)
                                        @foreach($filaCat['actividades'] as $actFila)
                                            <td class="px-3 py-3 text-center">
                                                @if($actFila['calificacion'] !== null)
                                                    <span class="font-mono font-semibold {{ $actFila['calificacion'] >= 70 ? 'text-slate-800' : 'text-red-600' }}">
                                                        {{ number_format($actFila['calificacion'], 1) }}
                                                    </span>
                                                @else
                                                    <span class="text-slate-300">–</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="px-3 py-3 text-center bg-orange-50/40">
                                            @if($filaCat['promedio'] !== null)
                                                <span class="font-bold {{ $filaCat['promedio'] >= 70 ? 'text-orange-700' : 'text-red-600' }}">
                                                    {{ number_format($filaCat['promedio'], 1) }}
                                                </span>
                                            @else
                                                <span class="text-slate-300">–</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center bg-slate-50/60">
                                        @if($fila['promedio_ponderado'] !== null)
                                            <span class="text-base font-bold {{ $fila['promedio_ponderado'] >= 70 ? 'text-slate-900' : 'text-red-600' }}">
                                                {{ number_format($fila['promedio_ponderado'], 1) }}
                                            </span>
                                        @else
                                            <span class="text-slate-300">–</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Alumnos de baja --}}
            @if($alumnosBaja->isNotEmpty())
                <div class="border-t border-slate-200/80 px-6 py-4">
                    <p class="mb-3 text-xs font-bold uppercase tracking-widest text-slate-400">Dados de baja</p>
                    <div class="space-y-2">
                        @foreach($alumnosBaja as $a)
                            <div class="flex items-center justify-between gap-4 rounded-2xl border border-red-100 bg-red-50/60 px-4 py-2.5">
                                <div>
                                    <p class="text-sm font-semibold text-red-700 line-through">{{ $a->nombre }}</p>
                                    <p class="font-mono text-xs text-red-400">{{ $a->matricula }}</p>
                                </div>
                                <form method="POST" action="{{ route('alumno.reinscribir', [$grupo, $a]) }}">
                                    @csrf
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 rounded-full border border-teal-200 bg-white px-3 py-1.5 text-xs font-semibold text-teal-700 hover:bg-teal-50 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                        Reinscribir
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

    </div>
</div>
@endsection
