<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <x-water-chip value="IA / Weka" label="Configuraciones guardadas" />
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Configuraciones de entrenamiento</h2>
                <p class="text-sm text-slate-500">Lista de configuraciones preparadas con target, algoritmo y snapshot de análisis del dataset.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-water-button href="{{ route('training-configurations.create') }}" class="bg-slate-950 text-white shadow-lg shadow-cyan-900/15">Nueva configuración</x-water-button>
                <x-water-button href="{{ route('datasets.index') }}" class="border border-slate-200 bg-white text-slate-700">Ir a datasets</x-water-button>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-4 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Configuraciones</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['total_configurations']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Persistidas y listas para entrenamiento.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Datasets usados</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['total_datasets']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Cobertura actual del catálogo.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Uso de modelos base</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['zeror'] + $statistics['oner'] + $statistics['naive_bayes'] + $statistics['logistic']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Configuraciones con ZeroR, OneR, NaiveBayes o Logistic.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Última creación</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $statistics['latest_created_at'] ? \Illuminate\Support\Carbon::parse($statistics['latest_created_at'])->format('d/m/Y H:i') : 'n/a' }}</p>
                    <p class="mt-2 text-xs text-slate-500">Actividad más reciente del módulo.</p>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                <form method="GET" action="{{ route('training-configurations.index') }}" class="grid gap-4 lg:grid-cols-[1fr_180px_auto] lg:items-end">
                    <div>
                        <label for="search" class="block text-sm font-medium text-slate-700">Buscar configuraciones</label>
                        <input id="search" name="search" value="{{ $search }}" placeholder="Dataset, target, algoritmo o creador" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100">
                    </div>
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-slate-700">Por página</label>
                        <select id="per_page" name="per_page" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100">
                            @foreach ([12, 24, 36, 48] as $value)
                                <option value="{{ $value }}" @selected((int) $perPage === $value)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-3 lg:justify-end">
                        <x-water-button type="submit" class="bg-slate-950 text-white shadow-lg shadow-cyan-900/10">Filtrar</x-water-button>
                        <x-water-button href="{{ route('training-configurations.index') }}" class="border border-slate-200 bg-white text-slate-700">Limpiar</x-water-button>
                    </div>
                </form>
            </section>

            @if ($configurations->isEmpty())
                <section class="overflow-hidden rounded-[32px] border border-dashed border-cyan-200 bg-[linear-gradient(160deg,rgba(14,165,233,0.06),rgba(255,255,255,0.95))] p-8 shadow-[0_24px_80px_rgba(14,165,233,0.08)]">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-center">
                        <div class="space-y-4">
                            <x-water-chip value="0 configuraciones" label="Módulo vacío" />
                            <h3 class="text-2xl font-semibold tracking-tight text-slate-950">Todavía no hay configuraciones de entrenamiento guardadas.</h3>
                            <p class="max-w-2xl text-sm leading-6 text-slate-600">Crea la primera configuración, selecciona un dataset con preview compatible y deja el pipeline listo para conectar con Java + Weka.</p>
                            <div class="flex flex-wrap gap-3">
                                <x-water-button href="{{ route('training-configurations.create') }}" class="bg-slate-950 text-white">Nueva configuración</x-water-button>
                                <x-water-button href="{{ route('datasets.index') }}" class="border border-slate-200 bg-white text-slate-700">Ver datasets</x-water-button>
                            </div>
                        </div>

                        <div class="grid gap-3 rounded-[28px] border border-slate-200 bg-white/80 p-5 backdrop-blur">
                            <div class="h-4 w-2/3 rounded-full bg-slate-200 animate-pulse"></div>
                            <div class="h-20 rounded-3xl bg-slate-100 animate-pulse"></div>
                            <div class="grid grid-cols-2 gap-3">
                                <div class="h-16 rounded-2xl bg-slate-100 animate-pulse"></div>
                                <div class="h-16 rounded-2xl bg-slate-100 animate-pulse"></div>
                            </div>
                            <div class="h-10 rounded-2xl bg-slate-100 animate-pulse"></div>
                        </div>
                    </div>
                </section>
            @else
                <section class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                    <div class="hidden overflow-x-auto md:block">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50/80">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Dataset</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Algoritmo</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Target</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Creador</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($configurations as $configuration)
                                    <tr class="transition hover:bg-slate-50/60">
                                        <td class="px-6 py-5 align-top">
                                            <div class="space-y-1">
                                                <div class="text-sm font-semibold text-slate-950">{{ $configuration->dataset?->name ?? 'n/d' }}</div>
                                                <div class="text-sm text-slate-500">{{ $configuration->dataset?->original_name ?? 'n/a' }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 align-top">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 bg-cyan-50 text-cyan-700 ring-cyan-200">{{ $configuration->algorithm->label() }}</span>
                                        </td>
                                        <td class="px-6 py-5 align-top text-sm text-slate-600">{{ $configuration->target_column }}</td>
                                        <td class="px-6 py-5 align-top text-sm text-slate-600">
                                            <div class="space-y-1">
                                                <div class="font-medium text-slate-900">{{ $configuration->creator?->name ?? 'Sin creador' }}</div>
                                                <div class="text-xs text-slate-400">{{ $configuration->created_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 align-top">
                                            <div class="flex justify-end gap-2">
                                                <x-water-button href="{{ route('training-configurations.show', $configuration) }}" class="border border-slate-200 bg-white text-slate-700">Ver</x-water-button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid gap-4 p-4 md:hidden">
                        @foreach ($configurations as $configuration)
                            <article class="rounded-[28px] border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-950">{{ $configuration->dataset?->name ?? 'n/d' }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $configuration->target_column }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 bg-cyan-50 text-cyan-700 ring-cyan-200">{{ $configuration->algorithm->label() }}</span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-slate-600">
                                    <div class="rounded-2xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Target</p>
                                        <p class="mt-1 font-semibold text-slate-950">{{ $configuration->target_column }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Creador</p>
                                        <p class="mt-1 font-semibold text-slate-950">{{ $configuration->creator?->name ?? 'Sin creador' }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <x-water-button href="{{ route('training-configurations.show', $configuration) }}" class="border border-slate-200 bg-white text-slate-700">Ver</x-water-button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <div>
                {{ $configurations->links() }}
            </div>
        </div>
    </div>
</x-app-layout>