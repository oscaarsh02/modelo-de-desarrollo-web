<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <span class="eyebrow">Seguridad</span>
            <h2 class="text-3xl font-bold">Confirmar contrasena</h2>
            <p class="text-sm leading-7 text-slate-600">
                Esta area requiere una confirmacion adicional antes de continuar.
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="password" value="Contrasena" />
                <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <x-primary-button class="w-full justify-center">
                Confirmar
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
