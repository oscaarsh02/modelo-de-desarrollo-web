@extends('layouts.app')

@section('content')
<div class="shell space-y-8">
    <section class="panel-strong overflow-hidden">
        <div class="flex flex-col gap-6 px-6 py-8 lg:flex-row lg:items-center lg:justify-between lg:px-10 lg:py-10">
            <div class="space-y-4">
                <span class="eyebrow">Busqueda docente</span>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Buscar profesor</h1>
                <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                    Encontra el profesor que necesitas por nombre o matrícula.
                </p>
            </div>

            <a href="{{ route('profesores.index') }}" class="btn-primary">
                Ver todos los profesores
            </a>
        </div>
    </section>

    <section class="panel overflow-hidden">
        <form action="{{ route('profesores.search') }}" method="GET" class="px-6 py-8 lg:px-10 lg:py-10">
            <div class="flex flex-col gap-4 lg:flex-row lg:gap-3">
                <div class="flex-1">
                    <input
                        type="text"
                        name="buscar"
                        value="{{ old('buscar', $query) }}"
                        placeholder="Escribe el nombre o matrícula..."
                        class="w-full rounded-lg border border-slate-200 px-4 py-3 text-sm focus:border-slate-400 focus:outline-none"
                    >
                </div>
                <button type="submit" class="btn-primary whitespace-nowrap">
                    Buscar
                </button>
                @if($query)
                    <a href="{{ route('profesores.search') }}" class="btn-secondary whitespace-nowrap">
                        Limpiar
                    </a>
                @endif
            </div>
        </form>
    </section>

    @if($query)
        <section class="panel overflow-hidden">
            <div class="px-6 py-8 lg:px-10 lg:py-10">
                <p class="mb-6 text-sm text-slate-600">
                    {{ $profesores->count() }} resultado(s) encontrado(s) para: <strong>"{{ $query }}"</strong>
                </p>

                @if($profesores->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm text-slate-700">
                            <thead class="border-b border-slate-200 bg-white/70">
                                <tr>
                                    <th class="px-6 py-4 font-semibold">Nombre</th>
                                    <th class="px-6 py-4 font-semibold">Matricula</th>
                                    <th class="px-6 py-4 font-semibold">Cuenta</th>
                                    <th class="px-6 py-4 font-semibold">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/80">
                                @foreach($profesores as $profesor)
                                    <tr class="bg-white/35">
                                        <td class="px-6 py-4 font-semibold text-slate-900">{{ $profesor->nombre }}</td>
                                        <td class="px-6 py-4 font-mono text-xs font-semibold">{{ $profesor->matricula }}</td>
                                        <td class="px-6 py-4">
                                            @if($profesor->user)
                                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-800">Activa</span>
                                            @else
                                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Sin usuario</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <form action="{{ route('profesores.reset-password', $profesor) }}" method="POST" class="inline">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="text-sm font-semibold text-slate-800 transition hover:text-slate-950"
                                                    onclick="return confirm('Se generara una nueva password para este profesor. Continuar?')"
                                                >
                                                    Regenerar password
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-6 text-center">
                        <p class="text-slate-600">
                            No se encontraron profesores con: <strong>"{{ $query }}"</strong>
                        </p>
                    </div>
                @endif
            </div>
        </section>
    @else
        <section class="panel overflow-hidden">
            <div class="px-6 py-12 text-center lg:px-10">
                <p class="text-slate-500">Ingresa un nombre o matrícula para buscar.</p>
            </div>
        </section>
    @endif
</div>
@endsection
