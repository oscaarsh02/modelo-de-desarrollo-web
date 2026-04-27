<x-app-layout>
    <div class="shell space-y-8">
        <section class="panel-strong overflow-hidden">
            <div class="px-6 py-8 lg:px-10 lg:py-10">
                <div class="space-y-6">
                    <span class="eyebrow">Panel administrativo</span>
                    <div class="space-y-4">
                        <h1 class="max-w-3xl text-4xl font-bold tracking-tight sm:text-5xl">
                            Equipos, App de control academico.
                        </h1>
                        <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                            La plataforma ya permite cargar horarios oficiales, revisar equipos por NRC, administrar docentes y mostrar a cada profesor sus grupos asignados.
                        </p>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('horarios.create') }}" class="btn-primary">Cargar horarios</a>
                        <a href="{{ route('horarios.index') }}" class="btn-secondary">Ver equipos</a>
                        <a href="{{ route('profesores.index') }}" class="btn-secondary">Ver docentes</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Docentes</p>
                <p class="premium-number mt-4">{{ $stats['profesores'] }}</p>
                <p class="mt-3 text-sm text-slate-600">Profesores registrados o detectados.</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Equipos</p>
                <p class="premium-number mt-4">{{ $stats['equipos'] }}</p>
                <p class="mt-3 text-sm text-slate-600">Grupos visibles como equipos.</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Alumnos</p>
                <p class="premium-number mt-4">{{ $stats['alumnos'] }}</p>
                <p class="mt-3 text-sm text-slate-600">Registros actualmente disponibles.</p>
            </article>
            <article class="metric-card">
                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-slate-500">Bloques</p>
                <p class="premium-number mt-4">{{ $stats['bloques'] }}</p>
                <p class="mt-3 text-sm text-slate-600">Sesiones importadas desde PDF.</p>
            </article>
        </section>

    </div>
</x-app-layout>
