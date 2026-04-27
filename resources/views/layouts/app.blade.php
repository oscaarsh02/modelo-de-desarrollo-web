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
            <div class="hero-orb -left-20 top-24 h-56 w-56 bg-orange-200/45"></div>
            <div class="hero-orb right-[-3rem] top-28 h-72 w-72 bg-teal-200/35"></div>
            <div class="hero-orb bottom-[-4rem] left-1/3 h-72 w-72 bg-amber-200/25"></div>
            <div class="pointer-events-none absolute inset-x-0 top-0 h-80 bg-gradient-to-b from-white/75 to-transparent"></div>

            <nav class="sticky top-0 z-50 border-b border-white/50 bg-[#f8f4ed]/78 backdrop-blur-2xl">
                <div class="shell flex min-h-[88px] items-center justify-between gap-6">
                    <a href="{{ auth()->check() ? route('dashboard') : url('/') }}" class="flex items-center gap-3">
                        <img src="{{ asset('images/Untitled.png') }}" alt="Logo" class="h-9 w-9 rounded-xl shadow-sm">
                        <div>
                            <p class="font-['Space_Grotesk'] text-xl font-bold tracking-tight text-slate-900">Equipos Academicos</p>
                            <p class="text-[11px] font-bold uppercase tracking-[0.32em] text-slate-500">Campus control suite</p>
                        </div>
                    </a>

                    <div class="hidden items-center gap-2 lg:flex">
                        @auth
                            @php
                                $role = auth()->user()->role;
                                $current = request()->route()?->getName();
                            @endphp

                            @if($role === 'admin')
                                <a href="{{ route('dashboard') }}" class="{{ $current === 'dashboard' ? 'nav-link-active' : 'nav-link' }}">Panel</a>
                                <a href="{{ route('horarios.index') }}" class="{{ str_starts_with($current ?? '', 'horarios.') ? 'nav-link-active' : 'nav-link' }}">Equipos</a>
                                <a href="{{ route('profesores.index') }}" class="{{ str_starts_with($current ?? '', 'profesores.') ? 'nav-link-active' : 'nav-link' }}">Docentes</a>
                            @elseif($role === 'alumno')
                                <a href="{{ route('alumno.dashboard') }}" class="{{ $current === 'alumno.dashboard' ? 'nav-link-active' : 'nav-link' }}">Mis materias</a>
                            @else
                                <a href="{{ route('profesor.grupos') }}" class="{{ $current === 'profesor.grupos' ? 'nav-link-active' : 'nav-link' }}">Mis equipos</a>
                            @endif
                        @endauth
                    </div>

                    <div class="flex items-center gap-3">
                        @auth
                            <div class="hidden rounded-full border border-slate-200/70 bg-white/75 px-4 py-2 shadow-sm md:block">
                                <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] uppercase tracking-[0.24em] text-slate-500">{{ auth()->user()->role }}</p>
                            </div>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn-secondary px-4 py-2.5">
                                    Salir
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary px-5 py-2.5">
                                Iniciar sesion
                            </a>
                        @endauth
                    </div>
                </div>
            </nav>

            @isset($header)
                <header class="shell pt-8">
                    {{ $header }}
                </header>
            @endisset

            <main class="relative pb-20 pt-8">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </body>
</html>
