<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <span class="eyebrow">Registro</span>
            <h2 class="text-3xl font-bold">Crear cuenta</h2>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="name" value="Nombre" />
                <x-text-input id="name" class="mt-2 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Correo" />
                <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Contrasena" />
                <x-text-input id="password" class="mt-2 block w-full" type="password" name="password" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" value="Confirmar contrasena" />
                <x-text-input id="password_confirmation" class="mt-2 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <a class="text-sm font-semibold text-slate-600 transition hover:text-slate-900" href="{{ route('login') }}">
                    Ya tengo cuenta
                </a>

                <x-primary-button>
                    Registrarme
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>
