@extends('layouts.app')

@section('content')
<div class="shell space-y-8">

    {{-- Header --}}
    <section class="panel-strong overflow-hidden">
        <div class="px-6 py-8 lg:px-10 lg:py-10 space-y-5">
            <a href="{{ route('alumno.dashboard') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-500 hover:text-slate-900 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Mi dashboard
            </a>
            <div class="space-y-3">
                <span class="eyebrow">Pase de lista</span>
                <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Mi código QR</h1>
                <p class="max-w-xl text-base leading-8 text-slate-600">
                    Muestra este código al profesor para registrar tu asistencia.
                </p>
            </div>
        </div>
    </section>

    @if($alumno)
        <section class="panel overflow-hidden">
            <div class="flex flex-col items-center gap-7 px-6 py-10 lg:py-14">

                {{-- Indicador de día de la semana --}}
                <div class="flex flex-col items-center gap-2">
                    <p class="text-xs font-semibold uppercase tracking-widest text-slate-400" id="fecha-hoy"></p>
                    <div class="flex gap-1.5" id="dias-strip">
                        @foreach(['L','M','M','J','V','S','D'] as $i => $d)
                            <div class="dia-pill flex h-9 w-9 items-center justify-center rounded-full text-xs font-bold transition-all"
                                 data-index="{{ $i }}"
                                 style="background:#f1f5f9; color:#94a3b8;">
                                {{ $d }}
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- QR --}}
                <div id="qr-container" class="rounded-[28px] bg-white p-6 shadow-lg ring-1 ring-slate-200"></div>

                {{-- Info alumno --}}
                <div class="space-y-2 text-center">
                    <p class="text-2xl font-bold">{{ $alumno->nombre }}</p>
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="rounded-full bg-slate-950 px-4 py-1.5 text-sm font-semibold text-white">{{ $alumno->matricula }}</span>
                        @if($alumno->correo)
                            <span class="rounded-full border border-slate-200 bg-white/80 px-4 py-1.5 text-sm font-semibold text-slate-600">{{ $alumno->correo }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 pt-1">El QR contiene tu matrícula: <strong>{{ $alumno->matricula }}</strong></p>
                </div>

            </div>
        </section>
    @else
        <section class="panel p-8">
            <div class="rounded-[24px] border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <h2 class="text-2xl font-bold">Cuenta sin vinculación</h2>
                <p class="mt-3 text-sm leading-7">Tu cuenta no está asociada a un alumno. Contacta a tu docente.</p>
            </div>
        </section>
    @endif

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    // QR
    const container = document.getElementById('qr-container');
    if (container) {
        new QRCode(container, {
            text: '{{ $alumno?->matricula ?? "" }}',
            width: 240,
            height: 240,
            colorDark: '#020617',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.H,
        });
    }

    // Indicador de día
    const hoy = new Date();
    // getDay() → 0=domingo, 1=lunes … 6=sábado
    // Nuestro arreglo: 0=L,1=M,2=M,3=J,4=V,5=S,6=D  (Lunes primero)
    const diasSemana = [1, 2, 3, 4, 5, 6, 0]; // índice JS por posición en el strip
    const diaPosicion = diasSemana.indexOf(hoy.getDay()); // posición en nuestro strip

    document.querySelectorAll('.dia-pill').forEach(el => {
        const idx = parseInt(el.dataset.index);
        if (idx === diaPosicion) {
            el.style.background = '#0f766e';
            el.style.color = '#ffffff';
            el.style.boxShadow = '0 4px 14px rgba(15,118,110,0.35)';
        }
    });

    const meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const nombresdia = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];
    document.getElementById('fecha-hoy').textContent =
        nombresdia[hoy.getDay()] + ' ' + hoy.getDate() + ' de ' + meses[hoy.getMonth()];
</script>
@endsection
