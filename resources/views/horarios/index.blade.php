@extends('layouts.app')

@section('content')
<div class="shell space-y-8">
    <section class="panel-strong overflow-hidden">
        <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.15fr,0.85fr] lg:px-10 lg:py-10">
            <div class="space-y-5">
                <span class="eyebrow">Mapa academico</span>
                <div class="space-y-4">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                        Equipos, materias y horarios con lectura clara y presentacion ejecutiva.
                    </h1>
                    <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        La programacion importada se organiza en tarjetas y tablas con mejor jerarquia visual, para que la revision sea rapida y la demo se vea solida.
                    </p>
                </div>

                @if(auth()->user() && auth()->user()->role === 'admin')
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('horarios.create') }}" class="btn-primary">Importar nuevo PDF</a>
                        <a href="{{ route('dashboard') }}" class="btn-secondary">Volver al panel</a>
                    </div>
                @endif
            </div>

            <div class="panel-dark p-6 text-white">
                <p class="text-xs font-bold uppercase tracking-[0.24em] text-slate-300">Mapa operativo</p>
                <h2 class="mt-3 text-3xl font-bold text-white">Lectura institucional</h2>
                <p class="mt-3 text-sm leading-7 text-slate-300">
                    La estructura privilegia NRC, grupo, profesor y salon para una revision administrativa mucho mas limpia.
                </p>

                <div class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-[22px] bg-white/10 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Formato</p>
                        <p class="mt-2 text-lg font-bold text-white">Resumen visual</p>
                    </div>
                    <div class="rounded-[22px] bg-white/10 p-4">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-300">Uso</p>
                        <p class="mt-2 text-lg font-bold text-white">Presentacion al cliente</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if (session('success'))
        <section class="panel p-5">
            <div class="rounded-[22px] border border-emerald-200 bg-emerald-50 p-5">
                <h2 class="text-lg font-bold text-emerald-900">Carga completada</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-emerald-800">{{ session('success') }}</p>
            </div>
        </section>
    @endif

    @if (session('error'))
        <section class="panel p-5">
            <div class="rounded-[22px] border border-rose-200 bg-rose-50 p-5">
                <h2 class="text-lg font-bold text-rose-900">Error en la importacion</h2>
                <p class="mt-2 text-sm leading-7 text-rose-800">{{ session('error') }}</p>
            </div>
        </section>
    @endif

    @if ($horariosAgrupados->count() > 0)
        @php
            $ordenDias = ['L' => 1, 'A' => 2, 'M' => 3, 'J' => 4, 'V' => 5, 'S' => 6];
        @endphp

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Pagina actual</p>
                <p class="premium-number mt-4">{{ $horariosAgrupados->count() }}</p>
                <p class="mt-3 text-sm text-slate-600">NRC visibles.</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Grupos</p>
                <p class="premium-number mt-4">{{ $horariosAgrupados->flatten(1)->pluck('grupo_id')->unique()->count() }}</p>
                <p class="mt-3 text-sm text-slate-600">Equipos en esta pagina.</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Con docente</p>
                <p class="premium-number mt-4">{{ $horariosAgrupados->flatten(1)->whereNotNull('profesor_id')->count() }}</p>
                <p class="mt-3 text-sm text-slate-600">Bloques ya vinculados.</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Estado</p>
                <p class="mt-4 text-2xl font-bold text-slate-900">Listo para demo</p>
                <p class="mt-3 text-sm text-slate-600">Lectura clara y ordenada.</p>
            </article>
        </section>

        <section class="space-y-6">
            @foreach ($horariosAgrupados as $nrc => $bloques)
                @php
                    $principal = $bloques->first();
                    $bloquesOrdenados = $bloques->sortBy(function ($item) use ($ordenDias) {
                        $ordenDia = $ordenDias[$item->dia] ?? 99;
                        return sprintf('%02d-%s', $ordenDia, $item->hora);
                    });
                @endphp

                <article class="panel overflow-hidden">
                    <div class="border-b border-slate-200/80 px-6 py-6">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div class="space-y-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="rounded-full bg-slate-950 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-white">NRC {{ $nrc }}</span>
                                    <span class="rounded-full bg-orange-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-orange-800">{{ $principal->grupo->materia->clave ?? 'SIN CLAVE' }}</span>
                                    <span class="rounded-full bg-teal-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-teal-800">Equipo {{ $principal->grupo->nombre ?? 'N/A' }}</span>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold">{{ $principal->grupo->materia->nombre ?? 'Sin nombre de materia' }}</h2>
                                    <p class="mt-1 text-sm text-slate-500">Resumen academico del equipo importado.</p>
                                </div>
                            </div>

                            <div class="metric-card min-w-[160px] px-5 py-4">
                                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Bloques</p>
                                <p class="mt-3 text-3xl font-bold text-slate-900">{{ $bloques->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 pb-6 pt-4">
                        <div class="table-shell">
                            <table class="min-w-full text-left text-sm text-slate-700">
                                <thead class="border-b border-slate-200 bg-white/80">
                                    <tr>
                                        <th class="px-6 py-4 font-semibold">Dia</th>
                                        <th class="px-6 py-4 font-semibold">Hora</th>
                                        <th class="px-6 py-4 font-semibold">Salon</th>
                                        <th class="px-6 py-4 font-semibold">Profesor</th>
                                        @if(auth()->user() && auth()->user()->role === 'admin')
                                            <th class="px-6 py-4 font-semibold">Accion</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/70">
                                    @foreach ($bloquesOrdenados as $horario)
                                        <tr class="bg-white/50 transition hover:bg-orange-50/40">
                                            <td class="px-6 py-4">
                                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-slate-700">{{ $horario->dia }}</span>
                                            </td>
                                            <td class="px-6 py-4 font-mono text-xs font-semibold">{{ $horario->hora }}</td>
                                            <td class="px-6 py-4">{{ $horario->salon->nombre ?? 'Sin salon' }}</td>
                                            <td class="px-6 py-4">
                                                @if ($horario->profesor)
                                                    <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">{{ $horario->profesor->nombre }}</span>
                                                @else
                                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Sin asignar</span>
                                                @endif
                                            </td>
                                            @if(auth()->user() && auth()->user()->role === 'admin')
                                                <td class="px-6 py-4">
                                                    <form action="{{ route('horarios.destroy', $horario) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            type="submit"
                                                            class="text-sm font-semibold text-rose-700 transition hover:text-rose-900"
                                                            onclick="return confirm('Estas seguro de que deseas eliminar este bloque de horario?')"
                                                        >
                                                            Eliminar bloque
                                                        </button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </article>
            @endforeach
        </section>

        <section class="panel p-5">
            {{ $nrcPaginator->links() }}
        </section>
    @else
        <section class="panel p-10 text-center">
            <h2 class="text-3xl font-bold">Aun no hay equipos cargados</h2>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-600">
                Importa el primer PDF de Secretaria Academica para crear materias, grupos, docentes y bloques de clase.
            </p>
            @if(auth()->user() && auth()->user()->role === 'admin')
                <div class="mt-6">
                    <a href="{{ route('horarios.create') }}" class="btn-primary">
                        Cargar primer PDF
                    </a>
                </div>
            @endif
        </section>
    @endif
</div>
@endsection
