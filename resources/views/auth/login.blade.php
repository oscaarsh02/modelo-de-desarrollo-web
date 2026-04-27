<x-guest-layout>
    <div class="space-y-6">
        <div class="space-y-2">
            <span class="eyebrow">Inicio de sesion</span>
            <h2 class="text-3xl font-bold">Entrar al sistema</h2>
            <p class="text-sm leading-7 text-slate-600">
                Usa tu matricula y tu contrasena para acceder a la vista correspondiente.
            </p>
        </div>

        <x-auth-session-status class="rounded-[18px] border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <x-input-label for="matricula" :value="__('Matricula')" />
                <x-text-input id="matricula"
                    class="mt-2 block w-full"
                    type="text"
                    name="matricula"
                    :value="old('matricula')"
                    required
                    autofocus />
                <x-input-error :messages="$errors->get('matricula')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Contrasena')" />
                <x-text-input id="password"
                    class="mt-2 block w-full"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4">
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-slate-600">
                    <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-orange-600 shadow-sm focus:ring-orange-500" name="remember">
                    <span>Recordarme</span>
                </label>

                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-slate-600 transition hover:text-slate-900" href="{{ route('password.request') }}">
                        Olvide mi contrasena
                    </a>
                @endif
            </div>

            <x-primary-button class="w-full justify-center">
                Entrar
            </x-primary-button>
        </form>
    </div>
</x-guest-layout>
