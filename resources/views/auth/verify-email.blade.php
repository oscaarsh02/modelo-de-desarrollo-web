<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <span class="eyebrow">Verificacion</span>
            <h2 class="text-3xl font-bold">Verifica tu correo</h2>
            <p class="text-sm leading-7 text-slate-600">
                Antes de continuar, revisa tu correo y abre el enlace de verificacion.
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="rounded-[18px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                Se envio un nuevo enlace de verificacion.
            </div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button>
                    Reenviar verificacion
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-semibold text-slate-600 transition hover:text-slate-900">
                    Cerrar sesion
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
