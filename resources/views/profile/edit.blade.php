<x-app-layout>
    <div class="shell space-y-8">
        <section class="panel-strong overflow-hidden">
            <div class="grid gap-8 px-6 py-8 lg:grid-cols-[1.2fr,0.8fr] lg:px-10 lg:py-10">
                <div class="space-y-4">
                    <span class="eyebrow">Perfil</span>
                    <h1 class="text-4xl font-bold tracking-tight sm:text-5xl">Configuracion de cuenta</h1>
                    <p class="max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        Administra la informacion basica de tu cuenta, actualiza la contrasena y controla las opciones personales del sistema.
                    </p>
                </div>

                <div class="grid gap-4">
                    <div class="metric-card">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Usuario</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ auth()->user()->name }}</p>
                    </div>
                    <div class="metric-card">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500">Rol</p>
                        <p class="mt-2 text-2xl font-bold text-slate-900">{{ auth()->user()->role }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6">
            <div class="panel p-6 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="panel p-6 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="panel p-6 sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
