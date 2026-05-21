@extends('layouts.app')

@section('content')
<div class="shell space-y-8">
    <section class="panel-strong overflow-hidden">
        <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.1fr,0.9fr] lg:px-10 lg:py-10">
            <div class="space-y-5">
                <a href="{{ route('horarios.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                    <span>&larr;</span>
                    <span>Volver al mapa de equipos</span>
                </a>

                <span class="eyebrow">Importar horarios</span>
                <div class="space-y-4">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">
                        Importar PDF de horarios.
                    </h1>
                    <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        Sube el PDF oficial de Secretaría Académica. El sistema registrará automáticamente materias, grupos, salones, horarios y docentes.
                    </p>
                </div>
            </div>

            <div class="soft-grid rounded-[28px] border border-slate-200/70 bg-white/55 p-5">
                <div class="space-y-4">
                    <div class="rounded-[24px] bg-slate-950 p-5 text-white">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-300">¿Qué se importa?</p>
                        <h2 class="mt-2 text-2xl font-bold">Datos del PDF</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-300">
                            NRC, clave, materia, grupo, día, hora, docente y salón quedan registrados en el sistema.
                        </p>
                    </div>

                    <div class="rounded-[24px] bg-white/85 p-5">
                        <p class="text-xs uppercase tracking-[0.24em] text-slate-500">Resultado</p>
                        <p class="mt-2 text-sm leading-7 text-slate-600">
                            Los grupos aparecen en el mapa de horarios y los docentes pueden ver sus grupos asignados al iniciar sesión.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($errors->any())
        <section class="panel p-5">
            <div class="rounded-[22px] border border-rose-200 bg-rose-50 p-5">
                <h2 class="text-lg font-bold text-rose-900">Revisa el archivo</h2>
                <ul class="mt-3 space-y-2 text-sm text-rose-800">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if (session('success'))
        <section class="panel p-5">
            <div class="rounded-[22px] border border-emerald-200 bg-emerald-50 p-5">
                <h2 class="text-lg font-bold text-emerald-900">Importacion completada</h2>
                <p class="mt-2 whitespace-pre-line text-sm leading-7 text-emerald-800">{{ session('success') }}</p>
                <div class="mt-4">
                    <a href="{{ route('horarios.index') }}" class="btn-primary">
                        Ver equipos creados
                    </a>
                </div>
            </div>
        </section>
    @endif

    @if (session('error'))
        <section class="panel p-5">
            <div class="rounded-[22px] border border-rose-200 bg-rose-50 p-5">
                <h2 class="text-lg font-bold text-rose-900">No se pudo procesar el PDF</h2>
                <p class="mt-2 text-sm leading-7 text-rose-800">{{ session('error') }}</p>
            </div>
        </section>
    @endif

    <section class="grid gap-6 xl:grid-cols-[1.05fr,0.95fr]">
        <article class="panel p-6 sm:p-8">
            <div class="mb-6">
                <h2 class="text-2xl font-bold">Subir archivo</h2>
                <p class="mt-2 text-sm leading-7 text-slate-600">Arrastra el PDF o selecciona el documento oficial para iniciar la importacion.</p>
            </div>

            <form action="{{ route('horarios.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div id="dropZone" class="rounded-[28px] border-2 border-dashed border-slate-300 bg-white/80 p-10 text-center transition">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-orange-100 text-orange-700">
                        <svg class="h-8 w-8" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                            <path d="M28 8H12a4 4 0 00-4 4v20a4 4 0 004 4h24a4 4 0 004-4V20m-18-8v12m-6-3l6-6 6 6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>

                    <label for="pdf" class="mt-5 block cursor-pointer">
                        <span class="text-lg font-bold text-slate-900">Selecciona el PDF de horarios</span>
                        <span class="mt-2 block text-sm text-slate-500">o arrástralo dentro de esta zona</span>
                    </label>

                    <input type="file" name="pdf" id="pdf" accept="application/pdf" class="hidden" required>

                    <div id="fileName" class="mt-6 rounded-[20px] bg-slate-50 px-4 py-3 text-sm font-medium text-slate-500">
                        Ningún archivo seleccionado
                    </div>
                </div>

                <button type="submit" id="submitBtn" class="btn-primary w-full disabled:cursor-not-allowed disabled:opacity-60" disabled>
                    Procesar horarios
                </button>
            </form>
        </article>

        <article class="panel p-6 sm:p-8">
            <h2 class="text-2xl font-bold">¿Cómo funciona?</h2>
            <div class="mt-6 space-y-4">
                <div class="rounded-[22px] bg-slate-950 p-5 text-white">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-300">Paso 1</p>
                    <p class="mt-3 text-sm font-medium leading-7 text-slate-200">
                        Sube el PDF oficial de Secretaría Académica con el horario del periodo.
                    </p>
                </div>

                <div class="rounded-[22px] bg-white/85 p-5">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Paso 2</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        El sistema extrae automáticamente NRC, materia, grupo, horario, salón y docente.
                    </p>
                </div>

                <div class="rounded-[22px] bg-white/85 p-5">
                    <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Paso 3</p>
                    <p class="mt-3 text-sm leading-7 text-slate-600">
                        Los grupos quedan disponibles en el sistema. Los docentes pueden iniciar sesión y ver sus grupos asignados.
                    </p>
                </div>
            </div>
        </article>
    </section>
</div>

<script>
    const fileInput = document.getElementById('pdf');
    const fileName = document.getElementById('fileName');
    const submitBtn = document.getElementById('submitBtn');
    const dropZone = document.getElementById('dropZone');

    function setSelectedFile(file) {
        fileName.textContent = file ? `Archivo listo: ${file.name}` : 'Ningún archivo seleccionado';
        submitBtn.disabled = !file;
    }

    fileInput.addEventListener('change', (event) => {
        setSelectedFile(event.target.files[0] ?? null);
    });

    dropZone.addEventListener('dragover', (event) => {
        event.preventDefault();
        dropZone.classList.add('border-orange-400', 'bg-orange-50');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('border-orange-400', 'bg-orange-50');
    });

    dropZone.addEventListener('drop', (event) => {
        event.preventDefault();
        dropZone.classList.remove('border-orange-400', 'bg-orange-50');

        if (event.dataTransfer.files.length > 0) {
            fileInput.files = event.dataTransfer.files;
            setSelectedFile(fileInput.files[0] ?? null);
        }
    });
</script>
@endsection
