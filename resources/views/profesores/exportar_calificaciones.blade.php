@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-3">
            <a href="{{ route('grupos.show', $grupo) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al grupo
            </a>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-2">
                    <span class="eyebrow">Exportar calificaciones</span>
                    <h1 class="text-4xl font-bold tracking-tight">{{ $grupo->materia->nombre ?? 'Grupo' }}</h1>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">{{ $grupo->materia->clave ?? '' }}</span>
                        <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">Equipo {{ $grupo->nombre }}</span>
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-teal-800">{{ $alumnos->count() }} alumno(s)</span>
                    </div>
                </div>
                <a href="{{ route('grupos.exportar.download', $grupo) }}"
                   class="inline-flex items-center gap-2 rounded-full px-6 py-3 text-sm font-semibold text-white transition"
                   style="background: linear-gradient(135deg,#16a34a 0%,#22c55e 100%); box-shadow:0 8px 24px rgba(22,163,74,0.28);">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Descargar Excel
                </a>
            </div>
        </div>
    </section>

    @if(session('success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if($categorias->isEmpty())
        <div class="panel px-6 py-12 text-center">
            <p class="text-slate-500">No hay categorías ni actividades configuradas en este grupo.</p>
            <a href="{{ route('grupos.show', $grupo) }}" class="mt-2 inline-block text-sm font-semibold text-orange-600 hover:underline">
                Configura categorías y actividades
            </a>
        </div>
    @elseif($alumnos->isEmpty())
        <div class="panel px-6 py-12 text-center">
            <p class="text-slate-500">No hay alumnos activos en este grupo.</p>
        </div>
    @else

    {{-- Resumen de ponderaciones --}}
    <section class="panel overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 py-5">
            <h2 class="text-xl font-bold">Categorías y ponderaciones</h2>
        </div>
        <div class="flex flex-wrap gap-3 px-6 py-4">
            @foreach($categorias as $cat)
            <div class="rounded-2xl border border-slate-200 bg-white px-4 py-3 min-w-[140px]">
                <p class="text-xs text-slate-500">{{ $cat->nombre }}</p>
                <p class="text-2xl font-bold text-slate-900">{{ $cat->ponderacion }}<span class="text-sm font-normal text-slate-400">%</span></p>
                <p class="text-xs text-slate-400">
                    {{ in_array($cat->id, $categoriasAsistencia ?? [], true) ? 'Asistencia automatica' : $cat->actividades->count() . ' actividad(es)' }}
                </p>
            </div>
            @endforeach
            <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-3 min-w-[140px]">
                <p class="text-xs text-orange-600">Total</p>
                <p class="text-2xl font-bold text-orange-800">{{ $categorias->sum('ponderacion') }}<span class="text-sm font-normal">%</span></p>
                @if($categorias->sum('ponderacion') != 100)
                    <p class="text-xs text-red-500 font-semibold">Debe sumar 100%</p>
                @else
                    <p class="text-xs text-emerald-600">Correcto</p>
                @endif
            </div>
        </div>
    </section>

    {{-- Tabla editable de calificaciones --}}
    <section class="panel overflow-hidden">
        <div class="border-b border-slate-200/80 px-6 py-5 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold">Ajuste de calificaciones</h2>
                <p class="mt-0.5 text-xs text-slate-500">Edita cualquier calificación en escala 0–10 antes de exportar. Los cambios se guardan al hacer clic en "Guardar ajustes".</p>
            </div>
        </div>

        <form method="POST" action="{{ route('grupos.exportar.guardar', $grupo) }}" id="form-ajustes">
            @csrf
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="bg-slate-900 text-white">
                            <th class="px-4 py-3 text-left font-semibold sticky left-0 bg-slate-900 z-10" rowspan="2">#</th>
                            <th class="px-4 py-3 text-left font-semibold sticky left-8 bg-slate-900 z-10 min-w-[200px]" rowspan="2">Alumno</th>
                            @foreach($categorias as $cat)
                                <th class="px-2 py-2 text-center font-semibold bg-slate-800 border-l border-slate-700"
                                    colspan="{{ in_array($cat->id, $categoriasAsistencia ?? [], true) ? 1 : ($cat->actividades->count() ?: 1) }}">
                                    {{ $cat->nombre }}
                                    <span class="ml-1 text-xs font-normal text-slate-400">({{ $cat->ponderacion }}%)</span>
                                </th>
                            @endforeach
                            <th class="px-4 py-3 text-center font-semibold bg-slate-700 border-l border-slate-600" rowspan="2">Cal. Final</th>
                        </tr>
                        <tr class="bg-slate-800 text-slate-300 text-xs">
                            @foreach($categorias as $cat)
                                @if(in_array($cat->id, $categoriasAsistencia ?? [], true))
                                    <th class="px-2 py-2 text-center font-medium border-l border-slate-700 max-w-[100px]">
                                        <span class="block truncate max-w-[100px]" title="Asistencias registradas">Asistencias</span>
                                    </th>
                                @else
                                @forelse($cat->actividades as $act)
                                    <th class="px-2 py-2 text-center font-medium border-l border-slate-700 max-w-[100px]">
                                        <span class="block truncate max-w-[100px]" title="{{ $act->nombre }}">{{ Str::limit($act->nombre, 18) }}</span>
                                    </th>
                                @empty
                                    <th class="px-2 py-2 text-center text-slate-500 border-l border-slate-700">—</th>
                                @endforelse
                                @endif
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" x-data>
                        @foreach($alumnos as $i => $alumno)
                        @php
                            $finalPonderado = 0;
                            $ponderacionTotal = $categorias->sum('ponderacion');
                            foreach ($categorias as $cat) {
                                $vals = in_array($cat->id, $categoriasAsistencia ?? [], true)
                                    ? collect([$matriz[$alumno->id]['asistencia_' . $cat->id] ?? null])->filter(fn($v) => $v !== null)
                                    : $cat->actividades->map(fn($act) => $matriz[$alumno->id][$act->id] ?? null)->filter(fn($v) => $v !== null);
                                if ($vals->isNotEmpty()) {
                                    $finalPonderado += $vals->average() * ($cat->ponderacion / 100);
                                }
                            }
                        @endphp
                        <tr class="{{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} hover:bg-orange-50/30 transition-colors"
                            x-data="{ calcular() {
                                // recalcular calificación final en el cliente
                            }}">
                            <td class="px-4 py-2 text-slate-400 font-mono text-xs sticky left-0 {{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 sticky left-8 {{ $i % 2 === 0 ? 'bg-white' : 'bg-slate-50' }} min-w-[200px]">
                                <p class="font-semibold text-slate-900">{{ $alumno->nombre }}</p>
                                <p class="font-mono text-xs text-slate-400">{{ $alumno->matricula }}</p>
                            </td>
                            @foreach($categorias as $cat)
                                @if(in_array($cat->id, $categoriasAsistencia ?? [], true))
                                <td class="px-2 py-2 text-center border-l border-slate-100">
                                    <input
                                        type="number"
                                        value="{{ $matriz[$alumno->id]['asistencia_' . $cat->id] ?? '' }}"
                                        min="0" max="10" step="0.01" readonly
                                        class="w-16 rounded-xl border border-teal-100 bg-teal-50 px-2 py-1 text-center text-sm font-mono text-teal-800
                                               [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        placeholder="—">
                                </td>
                                @else
                                @forelse($cat->actividades as $act)
                                <td class="px-2 py-2 text-center border-l border-slate-100">
                                    <input
                                        type="number"
                                        name="calificaciones[{{ $alumno->id }}][{{ $act->id }}]"
                                        value="{{ $matriz[$alumno->id][$act->id] ?? '' }}"
                                        min="0" max="10" step="0.01"
                                        class="w-16 rounded-xl border border-slate-200 bg-white px-2 py-1 text-center text-sm font-mono
                                               focus:outline-none focus:ring-2 focus:ring-orange-400
                                               [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                                        placeholder="—">
                                </td>
                                @empty
                                <td class="px-2 py-2 text-center border-l border-slate-100 text-slate-300">—</td>
                                @endforelse
                                @endif
                            @endforeach
                            <td class="px-4 py-2 text-center border-l border-slate-200 font-bold text-slate-900">
                                {{ $ponderacionTotal > 0 ? number_format($finalPonderado, 2) : '—' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end gap-3 border-t border-slate-200/80 px-6 py-5">
                <button type="submit" class="btn-primary">
                    Guardar ajustes
                </button>
            </div>
        </form>
    </section>

    @endif

</div>
@endsection
