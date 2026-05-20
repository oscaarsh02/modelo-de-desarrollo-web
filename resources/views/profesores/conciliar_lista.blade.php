@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-3">
            <a href="{{ route('grupos.importar.form', $grupo) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver a importar alumnos
            </a>
            <span class="eyebrow">Conciliación de lista — 2ª pasada</span>
            <h1 class="text-4xl font-bold tracking-tight">{{ $grupo->materia->nombre ?? 'Grupo' }}</h1>
            <p class="text-sm text-slate-500">
                Revisa los cambios detectados y confirma cuáles aplicar.
                Los alumnos dados de baja quedarán marcados como inactivos y no bloquearán el proceso.
            </p>
        </div>
    </section>

    <form method="POST" action="{{ route('grupos.conciliar.aplicar', $grupo) }}" class="space-y-6">
        @csrf

        {{-- Nuevos alumnos --}}
        @if($nuevos->isNotEmpty())
        <section class="panel overflow-hidden">
            <div class="border-b border-emerald-200 bg-emerald-50 px-6 py-4 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </span>
                <div>
                    <h2 class="font-bold text-emerald-900">Alumnos nuevos ({{ $nuevos->count() }})</h2>
                    <p class="text-xs text-emerald-700">Aparecen en el HTM pero no estaban registrados. Marca los que deseas dar de alta.</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($nuevos as $alumno)
                <label class="flex cursor-pointer items-center gap-4 px-6 py-3 hover:bg-slate-50">
                    <input type="checkbox" name="nuevos[]" value="{{ $alumno['matricula'] }}" checked
                           class="h-4 w-4 rounded border-slate-300 text-orange-500 focus:ring-orange-400">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $alumno['nombre'] }}</p>
                        <p class="font-mono text-xs text-slate-400">{{ $alumno['matricula'] }}</p>
                        @if($alumno['correo'])
                            <p class="text-xs text-slate-400">{{ $alumno['correo'] }}</p>
                        @endif
                    </div>
                    <span class="ml-auto rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-semibold text-emerald-700">Alta nueva</span>
                </label>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Posibles bajas --}}
        @if($posiblesBajas->isNotEmpty())
        <section class="panel overflow-hidden">
            <div class="border-b border-red-200 bg-red-50 px-6 py-4 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-red-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </span>
                <div>
                    <h2 class="font-bold text-red-900">Posibles bajas ({{ $posiblesBajas->count() }})</h2>
                    <p class="text-xs text-red-700">Estaban en el sistema pero no aparecen en el HTM. Marca los que confirmás como baja.</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($posiblesBajas as $alumno)
                <label class="flex cursor-pointer items-center gap-4 px-6 py-3 hover:bg-slate-50">
                    <input type="checkbox" name="bajas[]" value="{{ $alumno->matricula }}"
                           class="h-4 w-4 rounded border-slate-300 text-red-500 focus:ring-red-400">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $alumno->nombre }}</p>
                        <p class="font-mono text-xs text-slate-400">{{ $alumno->matricula }}</p>
                        @if($alumno->correo)
                            <p class="text-xs text-slate-400">{{ $alumno->correo }}</p>
                        @endif
                    </div>
                    <span class="ml-auto rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Posible baja</span>
                </label>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Reactivaciones --}}
        @if($reactivaciones->isNotEmpty())
        <section class="panel overflow-hidden">
            <div class="border-b border-blue-200 bg-blue-50 px-6 py-4 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-500 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                </span>
                <div>
                    <h2 class="font-bold text-blue-900">Reactivaciones ({{ $reactivaciones->count() }})</h2>
                    <p class="text-xs text-blue-700">Estaban dados de baja pero reaparecen en el HTM. Marca los que deseas reactivar.</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach($reactivaciones as $alumno)
                <label class="flex cursor-pointer items-center gap-4 px-6 py-3 hover:bg-slate-50">
                    <input type="checkbox" name="reactivar[]" value="{{ $alumno->matricula }}" checked
                           class="h-4 w-4 rounded border-slate-300 text-blue-500 focus:ring-blue-400">
                    <div>
                        <p class="text-sm font-semibold text-slate-900">{{ $alumno->nombre }}</p>
                        <p class="font-mono text-xs text-slate-400">{{ $alumno->matricula }}</p>
                    </div>
                    <span class="ml-auto rounded-full bg-blue-100 px-2 py-0.5 text-xs font-semibold text-blue-700">Reactivar</span>
                </label>
                @endforeach
            </div>
        </section>
        @endif

        {{-- Sin cambio --}}
        @if($sinCambio->isNotEmpty())
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200 px-6 py-4 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-slate-200 text-slate-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
                <div>
                    <h2 class="font-bold text-slate-700">Sin cambios ({{ $sinCambio->count() }})</h2>
                    <p class="text-xs text-slate-500">Alumnos que coinciden en ambas listas. No requieren acción.</p>
                </div>
            </div>
            <div class="divide-y divide-slate-100 max-h-52 overflow-y-auto">
                @foreach($sinCambio as $alumno)
                <div class="flex items-center gap-4 px-6 py-3 opacity-60">
                    <div>
                        <p class="text-sm font-semibold text-slate-700">{{ $alumno->nombre }}</p>
                        <p class="font-mono text-xs text-slate-400">{{ $alumno->matricula }}</p>
                    </div>
                    <span class="ml-auto rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-500">Sin cambio</span>
                </div>
                @endforeach
            </div>
        </section>
        @endif

        @if($nuevos->isEmpty() && $posiblesBajas->isEmpty() && $reactivaciones->isEmpty())
            <div class="rounded-[24px] border border-emerald-200 bg-emerald-50 px-6 py-6 text-center">
                <p class="font-semibold text-emerald-800">La lista está al día. No se detectaron diferencias.</p>
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <a href="{{ route('grupos.importar.form', $grupo) }}"
               class="rounded-full border border-slate-200 bg-white px-6 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                Cancelar
            </a>
            <button type="submit" class="btn-primary">
                Aplicar conciliación
            </button>
        </div>
    </form>

</div>
@endsection
