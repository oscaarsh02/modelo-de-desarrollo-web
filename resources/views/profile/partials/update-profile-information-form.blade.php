<section class="space-y-6">
    <header class="space-y-2">
        <h2 class="text-2xl font-bold text-slate-900">
            Informacion del perfil
        </h2>

        <p class="text-sm leading-7 text-slate-600">
            Actualiza los datos principales de tu cuenta.
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" value="Nombre" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        @if(isset($user->email))
            <div>
                <x-input-label for="email" value="Correo" />
                <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div class="rounded-[18px] border border-amber-200 bg-amber-50 px-4 py-3">
                        <p class="text-sm text-amber-900">
                            Tu correo aun no esta verificado.
                            <button form="send-verification" class="ml-1 font-semibold text-amber-800 underline">
                                Reenviar verificacion
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-emerald-700">
                                Se envio un nuevo enlace de verificacion.
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <x-primary-button>Guardar cambios</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-600"
                >Guardado.</p>
            @endif
        </div>
    </form>
</section>
