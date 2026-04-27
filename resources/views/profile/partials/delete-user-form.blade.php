<section class="space-y-6">
    <header class="space-y-2">
        <h2 class="text-2xl font-bold text-slate-900">
            Eliminar cuenta
        </h2>

        <p class="text-sm leading-7 text-slate-600">
            Esta accion elimina permanentemente el acceso de esta cuenta al sistema.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >Eliminar cuenta</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="space-y-5 p-6">
            @csrf
            @method('delete')

            <div>
                <h2 class="text-2xl font-bold text-slate-900">
                    Confirmar eliminacion
                </h2>

                <p class="mt-2 text-sm leading-7 text-slate-600">
                    Escribe tu contrasena para confirmar que deseas eliminar esta cuenta.
                </p>
            </div>

            <div>
                <x-input-label for="password" value="Contrasena" class="sr-only" />
                <x-text-input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full"
                    placeholder="Contrasena"
                />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">
                    Cancelar
                </x-secondary-button>

                <x-danger-button>
                    Eliminar definitivamente
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
