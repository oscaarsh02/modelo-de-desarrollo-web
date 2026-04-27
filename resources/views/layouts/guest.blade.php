<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Equipos Academicos') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/Untitled.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|space-grotesk:500,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="relative min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(196,85,45,0.18),_transparent_30%),radial-gradient(circle_at_80%_20%,_rgba(15,118,110,0.16),_transparent_25%)]"></div>

            <div class="shell relative z-10 grid min-h-screen items-center gap-10 py-10 lg:grid-cols-[1.1fr,0.9fr]">
                <div class="space-y-8">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/Untitled.png') }}" alt="Logo" class="h-11 w-11 rounded-2xl shadow-sm">
                        <div>
                            <p class="font-['Space_Grotesk'] text-lg font-bold text-slate-900">Equipos Academicos</p>
                            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Acceso al sistema</p>
                        </div>
                    </div>

                    <img src="{{ asset('images/mmmsi.png') }}" alt="Equipos Academicos"
                         class="h-24 w-24 drop-shadow-lg">

                    <div class="max-w-xl space-y-4">
                        <span class="eyebrow">Acceso</span>
                        <h1 class="text-5xl font-bold tracking-tight text-slate-900 sm:text-6xl">
                            Ingresa a la plataforma de gestion academica.
                        </h1>
                        <p class="text-base leading-8 text-slate-600 sm:text-lg">
                            Accede como administrador o profesor para consultar la programacion cargada, los equipos y los horarios disponibles en el sistema.
                        </p>
                    </div>
                </div>

                <div class="panel-strong px-6 py-8 sm:px-8">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
