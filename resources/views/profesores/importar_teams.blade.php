@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-3">
            <a href="{{ route('grupos.show', $grupo) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Volver al grupo
            </a>
            <span class="eyebrow">Calificaciones de Teams</span>
            <h1 class="text-4xl font-bold tracking-tight">{{ $grupo->materia->nombre ?? 'Grupo' }}</h1>
            <p class="text-sm text-slate-500">
                Sube el archivo Excel exportado desde Teams para importar las calificaciones de las tareas.
                El sistema las normaliza automáticamente a escala 0–10.
            </p>
        </div>
    </section>

    @if(session('error'))
        <div class="rounded-[24px] border border-red-200 bg-red-50 px-6 py-5 text-red-900 font-semibold">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1.2fr,1fr]">

        {{-- Formulario de carga --}}
        <section class="panel p-8 space-y-6">
            <div>
                <h2 class="text-2xl font-bold">Subir archivo de Teams</h2>
                <p class="mt-1 text-sm text-slate-500">
                    En Teams, ve a <strong>Calificaciones → Exportar</strong> y descarga el archivo Excel de la materia y grupo correctos.
                </p>
            </div>

            <form method="POST"
                  action="{{ route('grupos.teams.preview', $grupo) }}"
                  enctype="multipart/form-data"
                  class="space-y-5">
                @csrf

                <div x-data="{ nombre: null }">
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        Archivo Excel de Teams (.xlsx / .xls)
                    </label>
                    <label
                        :class="nombre ? 'border-emerald-400 bg-emerald-50' : 'border-slate-300 bg-white/60 hover:bg-white/80'"
                        class="flex flex-col items-center justify-center w-full h-40 rounded-[22px] border-2 border-dashed cursor-pointer transition-all duration-200">
                        <template x-if="!nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </template>
                        <template x-if="nombre">
                            <svg xmlns="http://www.w3.org/2000/svg" class="mb-2 h-10 w-10 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </template>
                        <p class="text-sm" :class="nombre ? 'text-emerald-700 font-semibold' : 'text-slate-500'">
                            <template x-if="!nombre"><span><span class="font-semibold text-orange-700">Haz clic</span> o arrastra el archivo aquí</span></template>
                            <template x-if="nombre"><span x-text="nombre"></span></template>
                        </p>
                        <p class="mt-1 text-xs text-slate-400" x-show="!nombre">Excel de calificaciones exportado desde Teams</p>
                        <input type="file" name="archivo" accept=".xlsx,.xls,.csv" class="hidden"
                               @change="nombre = $event.target.files[0]?.name ?? null" />
                    </label>
                    @error('archivo')
                        <div class="mt-3 rounded-[16px] border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn-primary w-full justify-center">
                    Vista previa de calificaciones
                </button>
            </form>
        </section>

        {{-- Info de categorías y actividades --}}
        <section class="panel overflow-hidden">
            <div class="border-b border-slate-200/80 px-6 py-5">
                <h2 class="text-xl font-bold">Actividades del grupo</h2>
                <p class="mt-0.5 text-xs text-slate-500">Las tareas de Teams se asignarán a estas actividades o se crearán nuevas.</p>
            </div>
            @if($grupo->categorias->isEmpty())
                <div class="px-6 py-8 text-center">
                    <p class="text-sm text-slate-400">No hay categorías definidas.</p>
                    <a href="{{ route('grupos.show', $grupo) }}" class="mt-2 inline-block text-sm font-semibold text-orange-600 hover:underline">
                        Configura categorías en el grupo
                    </a>
                </div>
            @else
                <div class="divide-y divide-slate-100 max-h-80 overflow-y-auto">
                    @foreach($grupo->categorias as $cat)
                        <div class="px-6 py-3">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-bold text-slate-800">{{ $cat->nombre }}</p>
                                <span class="rounded-full bg-orange-100 px-2 py-0.5 text-xs font-bold text-orange-800">{{ $cat->ponderacion }}%</span>
                            </div>
                            @if($cat->actividades->isEmpty())
                                <p class="mt-1 text-xs text-slate-400">Sin actividades</p>
                            @else
                                <ul class="mt-1 space-y-0.5">
                                    @foreach($cat->actividades as $act)
                                        <li class="text-xs text-slate-500">• {{ $act->nombre }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

</div>
@endsection
