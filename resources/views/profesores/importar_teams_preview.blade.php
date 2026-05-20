@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-3">
            <a href="{{ route('grupos.teams.form', $grupo) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver
            </a>
            <span class="eyebrow">Vista previa — Calificaciones de Teams</span>
            <h1 class="text-3xl font-bold tracking-tight">{{ $grupo->materia->nombre ?? 'Grupo' }}</h1>
            <p class="text-sm text-slate-500">
                Revisa el cruce de datos. Asigna cada tarea de Teams a una actividad del grupo o déjala como "Nueva actividad".
                Las calificaciones ya están normalizadas a escala 0–10.
            </p>
        </div>
    </section>

    <form method="POST" action="{{ route('grupos.teams.store', $grupo) }}" class="space-y-6">
        @csrf

        {{-- Mapeo de tareas → actividades --}}
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200/80 px-6 py-5">
                <h2 class="text-xl font-bold">Asignación de tareas de Teams</h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    Para cada tarea importada, elige a qué actividad del grupo corresponde.
                    Si eliges "Nueva actividad", se creará automáticamente en la categoría "Teams".
                </p>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($parsed['tareas'] as $tarea)
                <div class="flex flex-wrap items-center gap-4 px-6 py-4">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $tarea }}</p>
                        <p class="text-xs text-slate-400">Tarea de Teams</p>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    <div class="w-56">
                        <select name="mapeo[{{ $tarea }}]"
                                class="w-full rounded-2xl border border-slate-200 bg-white/70 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
                            <option value="nueva">+ Nueva actividad</option>
                            @foreach($grupo->categorias as $cat)
                                <optgroup label="{{ $cat->nombre }} ({{ $cat->ponderacion }}%)">
                                    @foreach($cat->actividades as $act)
                                        <option value="{{ $act->id }}"
                                            {{ $act->nombre === $tarea ? 'selected' : '' }}>
                                            {{ $act->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        {{-- Tabla de calificaciones por alumno --}}
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200/80 px-6 py-5">
                <h2 class="text-xl font-bold">Calificaciones detectadas</h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    Los alumnos con <span class="text-amber-600 font-semibold">fondo amarillo</span> no se encontraron en el grupo por correo o nombre.
                </p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-900 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold">Alumno (Teams)</th>
                            <th class="px-4 py-3 text-left font-semibold">Alumno en grupo</th>
                            @foreach($parsed['tareas'] as $tarea)
                            <th class="px-3 py-3 text-center font-semibold max-w-[120px] truncate" title="{{ $tarea }}">
                                {{ Str::limit($tarea, 20) }}
                            </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($filas as $fila)
                        <tr class="{{ $fila['alumno_id'] ? '' : 'bg-amber-50' }}">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-slate-800">{{ $fila['teams_nombre'] }}</p>
                                <p class="text-xs text-slate-400">{{ $fila['teams_correo'] }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @if($fila['alumno_id'])
                                    <p class="font-semibold text-slate-700">{{ $fila['alumno_nombre'] }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ $fila['matricula'] }}</p>
                                @else
                                    <span class="inline-block rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">No encontrado</span>
                                @endif
                            </td>
                            @foreach($parsed['tareas'] as $tarea)
                            <td class="px-3 py-3 text-center">
                                @php $cal = $fila['calificaciones'][$tarea] ?? null; @endphp
                                @if($cal !== null)
                                    <span class="font-mono {{ $cal == 0 ? 'text-red-500' : 'text-slate-700' }}">
                                        {{ number_format($cal, 2) }}
                                    </span>
                                @else
                                    <span class="text-slate-300">—</span>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="flex justify-end gap-3">
            <a href="{{ route('grupos.teams.form', $grupo) }}"
               class="rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                Confirmar e importar calificaciones
            </button>
        </div>
    </form>

</div>
@endsection
