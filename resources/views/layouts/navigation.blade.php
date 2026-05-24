<nav x-data="{ open: false }" class="sticky top-0 z-40 border-b border-sky-100 bg-white/80 backdrop-blur-xl shadow-[0_8px_32px_rgba(14,165,233,.08)]">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between gap-4">
            <div class="flex items-center gap-8">
                <a href="{{ route('dashboard') }}" class="group flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-sky-500 to-cyan-400 text-white shadow-lg shadow-sky-200 transition group-hover:scale-105">
                        <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c0 0-7 6.3-7 11.5a7 7 0 0 0 14 0C19 8.3 12 2 12 2z"/></svg>
                    </div>
                    <div class="hidden sm:block">
                        <div class="text-xs font-semibold uppercase tracking-[0.22em] text-sky-500">Water Quality</div>
                        <div class="text-sm font-semibold text-slate-900">Panel operativo</div>
                    </div>
                </a>

                <div class="hidden sm:flex items-center gap-2 rounded-full border border-sky-100 bg-slate-50/80 p-1 shadow-sm">
                    <a href="{{ route('dashboard') }}" class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-white text-sky-700 shadow-sm ring-1 ring-sky-100' : 'text-slate-600 hover:text-sky-700' }}">Dashboard</a>

                    <x-dropdown align="left" width="52">
                        <x-slot name="trigger">
                            <button class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('datasets.*') ? 'bg-white text-sky-700 shadow-sm ring-1 ring-sky-100' : 'text-slate-600 hover:text-sky-700' }}">
                                Datasets
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('datasets.index')">
                                Ver datasets
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('datasets.create')">
                                Subir dataset
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>

                    <x-dropdown align="left" width="56">
                        <x-slot name="trigger">
                            <button class="rounded-full px-4 py-2 text-sm font-medium transition {{ request()->routeIs('training-configurations.*') || request()->routeIs('training-jobs.*') ? 'bg-white text-sky-700 shadow-sm ring-1 ring-sky-100' : 'text-slate-600 hover:text-sky-700' }}">
                                IA
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('training-configurations.index')">
                                Configuraciones
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('training-configurations.create')">
                                Nueva configuración
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('training-jobs.index')">
                                Entrenamientos
                            </x-dropdown-link>
                            <x-dropdown-link :href="route('training-jobs.create')">
                                Nuevo entrenamiento
                            </x-dropdown-link>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center">
                <x-dropdown align="right" width="56">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-3 rounded-full border border-sky-100 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:-translate-y-0.5 hover:border-sky-200 hover:shadow-md focus:outline-none">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-cyan-400 text-xs font-bold text-white">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 text-sky-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-full border border-sky-100 bg-white p-2 text-sky-600 shadow-sm transition hover:bg-sky-50 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-sky-100 bg-white/95 backdrop-blur-xl">
        <div class="space-y-1 px-4 py-3">
            <a href="{{ route('dashboard') }}" class="block rounded-2xl px-4 py-3 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-sky-50 text-sky-700' : 'text-slate-600 hover:bg-sky-50 hover:text-sky-700' }}">Dashboard</a>

            <div class="rounded-2xl border border-sky-100 bg-slate-50/80 p-2">
                <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-500">Datasets</div>
                <a href="{{ route('datasets.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('datasets.index') ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:bg-white hover:text-sky-700' }}">Ver datasets</a>
                <a href="{{ route('datasets.create') }}" class="block rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('datasets.create') ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:bg-white hover:text-sky-700' }}">Subir dataset</a>
            </div>

            <div class="rounded-2xl border border-sky-100 bg-slate-50/80 p-2">
                <div class="px-3 py-2 text-[11px] font-semibold uppercase tracking-[0.18em] text-sky-500">IA</div>
                <a href="{{ route('training-configurations.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('training-configurations.index') ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:bg-white hover:text-sky-700' }}">Configuraciones</a>
                <a href="{{ route('training-configurations.create') }}" class="block rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('training-configurations.create') ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:bg-white hover:text-sky-700' }}">Nueva configuración</a>
                <a href="{{ route('training-jobs.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('training-jobs.index') ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:bg-white hover:text-sky-700' }}">Entrenamientos</a>
                <a href="{{ route('training-jobs.create') }}" class="block rounded-xl px-3 py-2 text-sm font-medium {{ request()->routeIs('training-jobs.create') ? 'bg-white text-sky-700 shadow-sm' : 'text-slate-600 hover:bg-white hover:text-sky-700' }}">Nuevo entrenamiento</a>
            </div>
        </div>

        <div class="border-t border-sky-100 px-4 py-4">
            <div class="mb-3">
                <div class="text-sm font-semibold text-slate-900">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-500">{{ Auth::user()->email }}</div>
            </div>
            <div class="space-y-1">
                <a href="{{ route('profile.edit') }}" class="block rounded-2xl px-4 py-3 text-sm font-medium text-slate-600 hover:bg-sky-50 hover:text-sky-700">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full rounded-2xl px-4 py-3 text-left text-sm font-medium text-slate-600 hover:bg-sky-50 hover:text-sky-700">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</nav>
