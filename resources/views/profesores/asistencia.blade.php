@extends('layouts.app')

@section('content')
{{-- Contenedor principal adaptado a móvil --}}
<div class="mx-auto max-w-2xl px-4 py-6 space-y-5" x-data="asistenciaScanner({{ $grupo->id }})">

    {{-- Header compacto --}}
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('grupos.show', $grupo) }}"
               class="inline-flex items-center gap-1 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Grupo
            </a>
            <h1 class="mt-1 text-xl font-bold leading-tight">{{ $grupo->materia->nombre ?? 'Grupo' }}</h1>
            <p class="text-xs text-slate-500">{{ $grupo->materia->clave ?? '' }} · Equipo {{ $grupo->nombre }}</p>
        </div>
        {{-- Contador --}}
        <div class="flex flex-col items-center rounded-2xl bg-teal-50 border border-teal-200 px-4 py-3 min-w-[72px]">
            <span class="text-3xl font-black text-teal-700" x-text="log.length">0</span>
            <span class="text-[10px] font-semibold uppercase tracking-widest text-teal-500">hoy</span>
        </div>
    </div>

    {{-- Cámara --}}
    <div class="relative overflow-hidden rounded-3xl bg-slate-950 shadow-2xl" style="aspect-ratio:1/1;">
        <div id="qr-reader" class="w-full h-full"></div>

        {{-- Overlay inactivo --}}
        <div x-show="!scannerActivo" x-cloak
             class="absolute inset-0 flex flex-col items-center justify-center gap-4 bg-slate-950">
            <div class="rounded-full bg-white/10 p-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-14 w-14 text-white/50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M3 9V5a2 2 0 012-2h4M3 15v4a2 2 0 002 2h4m10-16h-4a2 2 0 00-2 2v4m6 10h-4a2 2 0 01-2-2v-4"/>
                </svg>
            </div>
            <p class="text-sm font-semibold text-white/50">Cámara inactiva</p>
        </div>

        {{-- Visor activo --}}
        <div x-show="scannerActivo" x-cloak
             class="pointer-events-none absolute inset-0 flex items-center justify-center">
            <div class="relative h-56 w-56">
                <span class="absolute top-0 left-0 h-8 w-8 rounded-tl-xl border-t-4 border-l-4 border-teal-400"></span>
                <span class="absolute top-0 right-0 h-8 w-8 rounded-tr-xl border-t-4 border-r-4 border-teal-400"></span>
                <span class="absolute bottom-0 left-0 h-8 w-8 rounded-bl-xl border-b-4 border-l-4 border-teal-400"></span>
                <span class="absolute bottom-0 right-0 h-8 w-8 rounded-br-xl border-b-4 border-r-4 border-teal-400"></span>
                {{-- Línea de scan animada --}}
                <span class="absolute left-2 right-2 h-0.5 bg-teal-400/80 shadow-[0_0_8px_2px_rgba(45,212,191,0.6)]"
                      style="animation: scanline 2s ease-in-out infinite;"
                      :class="scannerActivo ? 'block' : 'hidden'"></span>
            </div>
        </div>

        {{-- Flash resultado sobre cámara --}}
        <div x-show="flashVisible" x-cloak
             :class="flashTipo === 'success' ? 'bg-emerald-500/90' : flashTipo === 'warning' ? 'bg-amber-500/90' : 'bg-red-500/90'"
             class="absolute inset-x-0 bottom-0 px-5 py-4 text-center text-white font-bold text-base transition-all duration-300">
            <p x-text="flashMensaje"></p>
            <p x-show="flashHora" class="text-sm font-normal opacity-80" x-text="flashHora"></p>
        </div>
    </div>

    <style>
        @keyframes scanline {
            0%   { top: 8px; opacity: 1; }
            50%  { top: calc(100% - 8px); opacity: 0.6; }
            100% { top: 8px; opacity: 1; }
        }
        #qr-reader video { object-fit: cover !important; width: 100% !important; height: 100% !important; }
        #qr-reader { border: none !important; }
        #qr-reader__scan_region { display: none !important; }
        #qr-reader__dashboard { display: none !important; }
    </style>

    {{-- Botones de control --}}
    <div class="grid grid-cols-2 gap-3">
        <button @click="iniciarScanner()" x-show="!scannerActivo"
                class="col-span-2 flex items-center justify-center gap-2 rounded-2xl py-4 text-base font-bold text-white shadow-lg active:scale-95 transition-transform"
                style="background:linear-gradient(135deg,#0f766e 0%,#14b8a6 100%); box-shadow:0 8px 24px rgba(15,118,110,0.35);">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 10l4.553-2.069A1 1 0 0121 8.87v6.26a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h8a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
            </svg>
            Activar cámara
        </button>

        <button @click="detenerScanner()" x-show="scannerActivo"
                class="flex items-center justify-center gap-2 rounded-2xl border border-slate-200 bg-white py-4 text-sm font-semibold text-slate-700 active:bg-slate-100 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
            </svg>
            Detener
        </button>

        <button @click="procesando = false" x-show="scannerActivo"
                class="flex items-center justify-center gap-2 rounded-2xl bg-slate-100 py-4 text-sm font-semibold text-slate-600 active:bg-slate-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Reiniciar
        </button>
    </div>

    {{-- Log de asistencias del día --}}
    <div class="panel overflow-hidden" x-show="log.length > 0" x-cloak>
        <div class="border-b border-slate-200/80 px-5 py-3 flex items-center justify-between">
            <h2 class="text-sm font-bold text-slate-700">Registradas hoy</h2>
            <span class="rounded-full bg-teal-100 px-2.5 py-0.5 text-xs font-bold text-teal-800" x-text="log.length"></span>
        </div>
        <div class="divide-y divide-slate-100 max-h-64 overflow-y-auto">
            <template x-for="(item, i) in log" :key="i">
                <div class="flex items-center justify-between px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="h-7 w-7 flex-shrink-0 rounded-full bg-teal-100 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-teal-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-slate-800" x-text="item.alumno"></span>
                    </div>
                    <span class="font-mono text-xs text-slate-400" x-text="item.hora"></span>
                </div>
            </template>
        </div>
    </div>

</div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
function asistenciaScanner(grupoId) {
    return {
        scannerActivo: false,
        flashVisible: false,
        flashTipo: 'success',
        flashMensaje: '',
        flashHora: '',
        log: [],
        procesando: false,
        html5QrCode: null,

        async iniciarScanner() {
            this.html5QrCode = new Html5Qrcode('qr-reader');
            const config = {
                fps: 15,
                qrbox: { width: 220, height: 220 },
                aspectRatio: 1.0,
                supportedScanTypes: [Html5QrcodeScanType.SCAN_TYPE_CAMERA],
            };
            try {
                await this.html5QrCode.start(
                    { facingMode: 'environment' },
                    config,
                    (decoded) => this.onScan(decoded)
                );
                this.scannerActivo = true;
            } catch (err) {
                // Intentar con cámara frontal como fallback
                try {
                    await this.html5QrCode.start(
                        { facingMode: 'user' },
                        config,
                        (decoded) => this.onScan(decoded)
                    );
                    this.scannerActivo = true;
                } catch {
                    this.mostrarFlash('error', 'Sin acceso a cámara. Verifica permisos del navegador.');
                }
            }
        },

        async detenerScanner() {
            if (this.html5QrCode) {
                try { await this.html5QrCode.stop(); } catch {}
                this.html5QrCode = null;
            }
            this.scannerActivo = false;
        },

        async onScan(matricula) {
            if (this.procesando) return;
            this.procesando = true;

            // Vibración táctil
            if (navigator.vibrate) navigator.vibrate(80);

            try {
                const res = await fetch('{{ route("asistencia.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ matricula, grupo_id: grupoId }),
                });

                const data = await res.json();

                if (res.status === 201) {
                    this.beep(880, 120);
                    if (navigator.vibrate) navigator.vibrate([60, 30, 60]);
                    this.mostrarFlash('success', '✓ ' + data.alumno, data.hora);
                    this.log.unshift({ alumno: data.alumno, hora: data.hora });
                } else if (data.warning) {
                    this.beep(440, 80);
                    this.mostrarFlash('warning', 'Ya registrado · ' + data.alumno);
                } else {
                    if (navigator.vibrate) navigator.vibrate([100, 50, 100]);
                    this.beep(220, 200);
                    this.mostrarFlash('error', data.error ?? 'Error desconocido');
                }
            } catch {
                this.mostrarFlash('error', 'Sin conexión al servidor');
            } finally {
                setTimeout(() => { this.procesando = false; }, 2500);
            }
        },

        mostrarFlash(tipo, mensaje, hora = '') {
            this.flashTipo = tipo;
            this.flashMensaje = mensaje;
            this.flashHora = hora;
            this.flashVisible = true;
            setTimeout(() => { this.flashVisible = false; }, 3000);
        },

        beep(freq, duration) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.frequency.value = freq;
                osc.type = 'sine';
                gain.gain.setValueAtTime(0.15, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration / 1000);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + duration / 1000);
            } catch {}
        },
    };
}
</script>
@endsection
