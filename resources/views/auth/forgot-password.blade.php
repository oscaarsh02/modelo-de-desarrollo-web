<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <span class="eyebrow">Recuperacion</span>
            <h2 class="text-3xl font-bold">Restablecer contrasena</h2>
            <p class="text-sm leading-7 text-slate-600">
                Ingresa tu correo para recibir un enlace de restablecimiento.
            </p>
        </div>

        <x-auth-session-status class="rounded-[18px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="email" value="Correo" />
                <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">
                Enviar enlace
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
