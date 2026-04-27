@extends('layouts.app')

@section('content')
<div class="shell space-y-8">
    <section class="panel-strong overflow-hidden">
        <div class="flex flex-col gap-6 px-6 py-8 lg:flex-row lg:items-end lg:justify-between lg:px-10 lg:py-10">
            <div class="space-y-4">
                <a href="{{ route('profesores.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                    <span>&larr;</span>
                    <span>Volver al directorio</span>
                </a>
                <span class="eyebrow">Alta de docente</span>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Registrar profesor</h1>
                <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                    Crea una cuenta docente manualmente para que el profesor pueda iniciar sesion en el sistema.
                </p>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <section class="panel p-5">
            <div class="rounded-[22px] border border-rose-200 bg-rose-50 p-5">
                <ul class="space-y-2 text-sm text-rose-800">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="grid gap-6 xl:grid-cols-[1fr,0.8fr]">
        <article class="panel p-6 sm:p-8">
            <form action="{{ route('profesores.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Nombre completo</label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100" required>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700">Matricula</label>
                    <input type="text" name="matricula" value="{{ old('matricula') }}" class="mt-2 block w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-orange-400 focus:ring-4 focus:ring-orange-100" required>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn-primary">
                        Guardar profesor
                    </button>
                    <a href="{{ route('profesores.index') }}" class="btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </article>

        <article class="panel p-6 sm:p-8">
            <h2 class="text-2xl font-bold">Que sucede al registrar</h2>
            <div class="mt-6 space-y-4">
                <div class="rounded-[22px] bg-slate-950 p-5 text-white">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-300">Cuenta</p>
                    <p class="mt-3 text-sm leading-7 text-slate-300">Se crea el profesor y se genera una contrasena temporal para su acceso.</p>
                </div>
                <div class="rounded-[22px] bg-white/85 p-5">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Uso</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">El docente podra entrar y ver sus equipos cuando tenga grupos vinculados.</p>
                </div>
            </div>
        </article>
    </section>
</div>
@endsection
