@extends('layouts.app')

@section('content')
<div class="shell space-y-8">
    <section class="panel-strong overflow-hidden">
        <div class="flex flex-col gap-6 px-6 py-8 lg:flex-row lg:items-end lg:justify-between lg:px-10 lg:py-10">
            <div class="space-y-4">
                <span class="eyebrow">Gestion docente</span>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Directorio de profesores</h1>
                <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                    Desde aqui controlas las cuentas docentes que despues operaran sus equipos, grupos y calificaciones.
                </p>
            </div>

            <div class="flex flex-col gap-3 lg:flex-row">
                <a href="{{ route('profesores.search') }}" class="btn-secondary">
                    Buscar profesor
                </a>
                <a href="{{ route('profesores.create') }}" class="btn-primary">
                    Nuevo profesor
                </a>
            </div>
        </div>
    </section>

    @if (session('success'))
        <section class="panel p-5">
            <div class="rounded-[22px] border border-emerald-200 bg-emerald-50 p-5">
                <p class="whitespace-pre-line text-sm leading-7 text-emerald-800">{{ session('success') }}</p>
            </div>
        </section>
    @endif

    <section class="panel overflow-hidden">
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
                    @forelse($profesores as $profesor)
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
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-10 text-center text-sm text-slate-500">
                                No hay profesores registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
