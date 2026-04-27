@extends('layouts.app')

@section('content')
<div class="shell space-y-6">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-4">
            <a href="{{ route('grupos.show', $grupo) }}"
               class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Detalle del grupo
            </a>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="space-y-3">
                    <span class="eyebrow">Pase de lista manual</span>
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">{{ $grupo->materia->nombre ?? 'Grupo' }}</h1>
                    <div class="flex flex-wrap gap-2">
                        <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">{{ $grupo->materia->clave ?? 'SIN CLAVE' }}</span>
                        <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">Equipo {{ $grupo->nombre }}</span>
                        <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-teal-800">{{ $alumnos->count() }} alumno(s)</span>
                    </div>
                </div>
                <a href="{{ route('asistencia.scanner', $grupo) }}"
                   class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4m10-16h-4a2 2 0 00-2 2v4m6 10h-4a2 2 0 01-2-2v-4"/>
                    </svg>
                    Cambiar a QR
                </a>
            </div>
        </div>
    </section>

    {{-- Flash --}}
    @if(session('success'))
        <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-6 py-4 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    {{-- Formulario --}}
    <form method="POST" action="{{ route('asistencia.manual.store', $grupo) }}"
          x-data="listaManual({{ $alumnos->pluck('id')->toJson() }}, {{ json_encode(array_map('intval', $presentes)) }})">
        @csrf

        <div class="panel overflow-hidden">

            {{-- Toolbar --}}
            <div class="border-b border-slate-200/80 px-6 py-4 space-y-3">
                <div class="flex items-center gap-3">
                    <input type="date" name="fecha" x-model="fecha"
                           max="{{ now()->toDateString() }}"
                           class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <span class="text-sm text-slate-500">
                        <span class="font-bold text-teal-700" x-text="presentes.length"></span>
                        presentes ·
                        <span class="font-bold text-red-500" x-text="{{ $alumnos->count() }} - presentes.length"></span>
                        faltas
                    </span>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="marcarTodosBtn()"
                            style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; background:#10b981; color:#fff; font-weight:700; font-size:0.875rem; padding:10px 0; border-radius:14px; border:none; cursor:pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                        Todos presentes
                    </button>
                    <button type="button" onclick="quitarTodosBtn()"
                            style="flex:1; display:flex; align-items:center; justify-content:center; gap:6px; background:#ef4444; color:#fff; font-weight:700; font-size:0.875rem; padding:10px 0; border-radius:14px; border:none; cursor:pointer;">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Todos falta
                    </button>
                </div>
            </div>

            {{-- Lista de alumnos --}}
            @if($alumnos->isEmpty())
                <div class="px-6 py-12 text-center text-sm text-slate-400">
                    No hay alumnos activos en este grupo.
                </div>
            @else
                <div class="divide-y divide-slate-100">
                    @foreach($alumnos as $i => $alumno)
                        <div @click="toggle({{ $alumno->id }})"
                             class="flex cursor-pointer items-center justify-between gap-2 px-4 py-3 transition-colors"
                             :class="presentes.includes({{ $alumno->id }}) ? 'bg-emerald-50/50 hover:bg-emerald-50' : 'bg-red-50/30 hover:bg-red-50/60'">

                            {{-- Avatar + nombre --}}
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="hidden sm:block w-5 flex-shrink-0 text-right text-xs font-semibold text-slate-400">{{ $i + 1 }}</span>
                                {{-- Círculo izquierdo: verde=presente, rojo=falta --}}
                                <div :style="`height:36px;width:36px;flex-shrink:0;border-radius:9999px;display:flex;align-items:center;justify-content:center;font-size:0.875rem;font-weight:700;color:#fff;background:${presentes.includes({{ $alumno->id }}) ? '#10b981' : '#f87171'}`">
                                    {{ mb_strtoupper(mb_substr($alumno->nombre, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-semibold text-slate-900">{{ $alumno->nombre }}</p>
                                    <p class="font-mono text-xs text-slate-400">{{ $alumno->matricula }}</p>
                                </div>
                            </div>

                            {{-- Estado --}}
                            <div class="flex flex-shrink-0 items-center gap-2">
                                <span class="hidden sm:block text-xs font-semibold transition"
                                      :style="presentes.includes({{ $alumno->id }}) ? 'color:#059669;' : 'color:#f87171;'">
                                    <span x-show="presentes.includes({{ $alumno->id }})">Presente</span>
                                    <span x-show="!presentes.includes({{ $alumno->id }})">Falta</span>
                                </span>

                                {{-- Paloma / Tacha --}}
                                <div :style="`height:40px;width:40px;flex-shrink:0;border-radius:9999px;display:flex;align-items:center;justify-content:center;color:#fff;background:${presentes.includes({{ $alumno->id }}) ? '#10b981' : '#f87171'}`">
                                    <svg x-show="presentes.includes({{ $alumno->id }})"
                                         xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <svg x-show="!presentes.includes({{ $alumno->id }})"
                                         xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>

                                <input type="checkbox" name="presentes[]" value="{{ $alumno->id }}"
                                       class="hidden" :checked="presentes.includes({{ $alumno->id }})">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Footer --}}
            <div class="border-t border-slate-200/80 px-6 py-5 flex items-center justify-between gap-4">
                <p class="text-sm text-slate-400">Toca cada alumno para cambiar su estado</p>
                <button type="submit" class="btn-primary px-8 justify-center">
                    Guardar lista
                </button>
            </div>

        </div>
    </form>

</div>

<script>
let _alpineInstance = null;

function marcarTodosBtn() {
    if (_alpineInstance) _alpineInstance.marcarTodos();
}
function quitarTodosBtn() {
    if (_alpineInstance) _alpineInstance.quitarTodos();
}

function listaManual(todos, iniciales) {
    return {
        fecha: '{{ $fecha }}',
        presentes: iniciales,
        init() { _alpineInstance = this; },
        toggle(id) {
            const idx = this.presentes.indexOf(id);
            if (idx === -1) {
                this.presentes.push(id);
            } else {
                this.presentes.splice(idx, 1);
            }
            this.$nextTick(() => {
                document.querySelectorAll('input[name="presentes[]"]').forEach(cb => {
                    cb.checked = this.presentes.includes(parseInt(cb.value));
                });
            });
        },
        marcarTodos() {
            this.presentes = [...todos];
            this.$nextTick(() => {
                document.querySelectorAll('input[name="presentes[]"]').forEach(cb => cb.checked = true);
            });
        },
        quitarTodos() {
            this.presentes = [];
            this.$nextTick(() => {
                document.querySelectorAll('input[name="presentes[]"]').forEach(cb => cb.checked = false);
            });
        },
    };
}
</script>
@endsection
