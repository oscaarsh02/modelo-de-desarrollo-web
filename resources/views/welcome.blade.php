<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipos Académicos</title>
    <link rel="icon" type="image/png" href="{{ asset('images/Untitled.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=manrope:400,500,600,700,800|space-grotesk:500,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes floatLogo {
            0%, 100% { transform: translateY(0px); }
            50%       { transform: translateY(-14px); }
        }
        @keyframes orbDrift {
            0%, 100% { transform: scale(1) translate(0, 0); }
            50%       { transform: scale(1.08) translate(12px, -10px); }
        }

        .anim-fade-up          { animation: fadeInUp 0.7s ease both; }
        .anim-fade-up-d1       { animation: fadeInUp 0.7s 0.1s ease both; }
        .anim-fade-up-d2       { animation: fadeInUp 0.7s 0.22s ease both; }
        .anim-fade-up-d3       { animation: fadeInUp 0.7s 0.34s ease both; }
        .anim-fade-up-d4       { animation: fadeInUp 0.7s 0.46s ease both; }
        .anim-fade-up-d5       { animation: fadeInUp 0.7s 0.58s ease both; }
        .anim-fade-up-d6       { animation: fadeInUp 0.7s 0.70s ease both; }
        .anim-fade-in          { animation: fadeIn 1s 0.1s ease both; }
        .anim-float            { animation: floatLogo 5s ease-in-out infinite; }
        .anim-orb              { animation: orbDrift 9s ease-in-out infinite; }
        .anim-orb-slow         { animation: orbDrift 13s ease-in-out infinite reverse; }

        .card-hover {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 40px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>
    <div class="relative min-h-screen overflow-hidden flex flex-col">

        {{-- Orbs animados --}}
        <div class="hero-orb anim-orb -left-24 -top-10 h-[30rem] w-[30rem] bg-orange-200/40"></div>
        <div class="hero-orb anim-orb-slow right-[-5rem] top-10 h-[34rem] w-[34rem] bg-teal-200/32"></div>
        <div class="hero-orb anim-orb bottom-[-5rem] left-1/3 h-80 w-80 bg-amber-200/22"></div>

        {{-- Header --}}
        <header class="shell relative z-10 flex items-center justify-between py-7 anim-fade-in">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/Untitled.png') }}" alt="Equipos Académicos" class="h-10 w-10 rounded-2xl shadow-sm">
                <div>
                    <p class="font-['Space_Grotesk'] text-lg font-bold leading-none text-slate-900">Equipos Académicos</p>
                    <p class="text-[10px] font-bold uppercase tracking-[0.28em] text-slate-400">Gestión escolar</p>
                </div>
            </div>
            @auth
                <a href="{{ route('dashboard') }}" class="text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                    Mi panel →
                </a>
            @endauth
        </header>

        {{-- Hero --}}
        <main class="relative z-10 flex flex-1 flex-col items-center justify-center px-6 pb-12 pt-6 text-center">

            {{-- Logo flotante --}}
            <div class="anim-fade-up mb-5">
                <img src="{{ asset('images/mmmsi.png') }}" alt="Logo"
                     class="anim-float mx-auto h-16 w-16 sm:h-20 sm:w-20 drop-shadow-lg">
            </div>

            <span class="eyebrow anim-fade-up-d1 mb-3">Plataforma académica</span>

            <h1 class="anim-fade-up-d2 max-w-xl text-4xl font-bold tracking-tight text-slate-900 sm:text-5xl lg:text-6xl lg:leading-tight">
                Todo tu grupo<br>en un solo lugar.
            </h1>

            <p class="anim-fade-up-d3 mt-4 max-w-md text-base leading-7 text-slate-500 sm:text-lg sm:leading-8">
                Registra asistencias con QR, consulta calificaciones y administra tus grupos desde cualquier dispositivo.
            </p>

            <div style="height: 1.5rem"></div>

            <div class="anim-fade-up-d4">
                <a href="{{ route('login') }}"
                   class="btn-primary px-12 py-4 text-base shadow-lg hover:shadow-xl transition-shadow">
                    Iniciar sesión
                </a>
            </div>

            <div style="height: 3rem"></div>

            {{-- Separador --}}
            <div class="anim-fade-up-d4 flex items-center gap-4 w-full max-w-2xl">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="text-xs font-semibold uppercase tracking-widest text-slate-400">Qué puedes hacer</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            {{-- 3 cards --}}
            <div class="mt-8 grid w-full max-w-2xl gap-4 sm:grid-cols-3">

                <div class="anim-fade-up-d4 card-hover rounded-[22px] border border-slate-200/80 bg-white/75 p-6 text-left backdrop-blur-sm">
                    <div class="mb-4 h-10 w-10 rounded-xl bg-teal-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4m10-16h-4a2 2 0 00-2 2v4m6 10h-4a2 2 0 01-2-2v-4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-900 sm:text-base">Asistencia QR</p>
                    <p class="mt-2 text-xs leading-5 text-slate-500 sm:text-sm sm:leading-6">Escanea el QR del alumno y registra su asistencia al instante.</p>
                </div>

                <div class="anim-fade-up-d5 card-hover rounded-[22px] border border-slate-200/80 bg-white/75 p-6 text-left backdrop-blur-sm">
                    <div class="mb-4 h-10 w-10 rounded-xl bg-orange-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-orange-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-900 sm:text-base">Calificaciones</p>
                    <p class="mt-2 text-xs leading-5 text-slate-500 sm:text-sm sm:leading-6">Captura y consulta promedios ponderados por categoría y actividad.</p>
                </div>

                <div class="anim-fade-up-d6 card-hover rounded-[22px] border border-slate-200/80 bg-white/75 p-6 text-left backdrop-blur-sm">
                    <div class="mb-4 h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-bold text-slate-900 sm:text-base">Grupos</p>
                    <p class="mt-2 text-xs leading-5 text-slate-500 sm:text-sm sm:leading-6">Importa alumnos, organiza materias y gestiona tus equipos fácilmente.</p>
                </div>

            </div>
        </main>

        {{-- Footer --}}
        <footer class="relative z-10 py-8 anim-fade-in">
            <div class="flex flex-col items-center gap-3">
                <img src="{{ asset('images/Untitled.png') }}" alt="Logo" class="h-7 w-7 rounded-lg opacity-60">
                <p class="text-xs text-slate-400">Equipos Académicos · Sistema de gestión escolar</p>
            </div>
        </footer>

    </div>
</body>
</html>
