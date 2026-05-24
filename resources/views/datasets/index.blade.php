<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <x-water-chip value="ML / Weka" label="Pipeline de datasets" />
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Datasets para entrenamiento automático</h2>
                <p class="text-sm text-slate-500">Sube CSVs validados, revisa metadata, controla su ciclo de vida y deja listo el material para Weka.</p>
            </div>
            <x-water-button href="{{ route('datasets.create') }}" class="whitespace-nowrap bg-slate-950 text-white shadow-lg shadow-cyan-900/15 hover:-translate-y-0.5">
                Subir dataset
            </x-water-button>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-4 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-4 text-sm text-rose-800 shadow-sm">
                    {{ session('error') }}
                </div>
            @endif

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Datasets</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['total_datasets']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Totales visibles en el catálogo.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Filas procesadas</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['total_rows']) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Registros listos para entrenamiento.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Columnas promedio</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['average_columns'], 1) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Esquema medio del catálogo.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Tamaño total</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['total_size'] / 1024 / 1024, 2) }} MB</p>
                    <p class="mt-2 text-xs text-slate-500">Archivos en storage/app/weka/datasets.</p>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-[0_20px_80px_rgba(2,132,199,0.08)]">
                <form method="GET" action="{{ route('datasets.index') }}" class="grid gap-4 lg:grid-cols-[1fr_180px_auto] lg:items-end">
                    <div>
                        <label for="search" class="block text-sm font-medium text-slate-700">Buscar datasets</label>
                        <input id="search" name="search" value="{{ $search }}" placeholder="Nombre, archivo original, estado o uploader" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100">
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
                        <x-water-button href="{{ route('datasets.index') }}" class="border border-slate-200 bg-white text-slate-700">Limpiar</x-water-button>
                    </div>
                </form>
            </section>

            @if ($datasets->isEmpty())
                <section class="overflow-hidden rounded-[32px] border border-dashed border-cyan-200 bg-[linear-gradient(160deg,rgba(14,165,233,0.06),rgba(255,255,255,0.95))] p-8 shadow-[0_24px_80px_rgba(14,165,233,0.08)]">
                    <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_340px] lg:items-center">
                        <div class="space-y-4">
                            <x-water-chip value="0 datasets" label="Catálogo vacío" />
                            <h3 class="text-2xl font-semibold tracking-tight text-slate-950">El catálogo está listo para tu primer dataset.</h3>
                            <p class="max-w-2xl text-sm leading-6 text-slate-600">Carga un CSV validado y el sistema extraerá filas, columnas, tamaño y metadata inmediata para dejarlo preparado para el pipeline de Weka.</p>
                            <div class="flex flex-wrap gap-3">
                                <x-water-button href="{{ route('datasets.create') }}" class="bg-slate-950 text-white">Subir CSV</x-water-button>
                                <x-water-button href="{{ route('dashboard') }}" class="border border-slate-200 bg-white text-slate-700">Ir al dashboard</x-water-button>
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
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Métrica</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Uploader</th>
                                    <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @foreach ($datasets as $dataset)
                                    <tr class="transition hover:bg-slate-50/60">
                                        <td class="px-6 py-5 align-top">
                                            <div class="space-y-1">
                                                <div class="text-sm font-semibold text-slate-950">{{ $dataset->name }}</div>
                                                <div class="text-sm text-slate-500">{{ $dataset->original_name }}</div>
                                                <div class="text-xs text-slate-400">{{ number_format($dataset->file_size / 1024, 2) }} KB</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 align-top">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $dataset->status->badgeClasses() }}">{{ $dataset->status->label() }}</span>
                                        </td>
                                        <td class="px-6 py-5 align-top text-sm text-slate-600">
                                            <div class="space-y-1">
                                                <div>{{ number_format($dataset->rows_count) }} filas</div>
                                                <div>{{ number_format($dataset->columns_count) }} columnas</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 align-top text-sm text-slate-600">
                                            <div class="space-y-1">
                                                <div class="font-medium text-slate-900">{{ $dataset->uploader?->name ?? 'Sin uploader' }}</div>
                                                <div class="text-xs text-slate-400">{{ $dataset->created_at?->format('d/m/Y H:i') }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-5 align-top">
                                            <div class="flex justify-end gap-2">
                                                <x-water-button href="{{ route('datasets.show', $dataset) }}" class="border border-slate-200 bg-white text-slate-700">Ver</x-water-button>
                                                <x-water-button href="{{ route('datasets.download', $dataset) }}" class="bg-cyan-500 text-white shadow-lg shadow-cyan-500/20">Descargar</x-water-button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="grid gap-4 p-4 md:hidden">
                        @foreach ($datasets as $dataset)
                            <article class="rounded-[28px] border border-slate-200 bg-slate-50/70 p-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h3 class="text-base font-semibold text-slate-950">{{ $dataset->name }}</h3>
                                        <p class="mt-1 text-sm text-slate-500">{{ $dataset->original_name }}</p>
                                    </div>
                                    <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ $dataset->status->badgeClasses() }}">{{ $dataset->status->label() }}</span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm text-slate-600">
                                    <div class="rounded-2xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Filas</p>
                                        <p class="mt-1 font-semibold text-slate-950">{{ number_format($dataset->rows_count) }}</p>
                                    </div>
                                    <div class="rounded-2xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Columnas</p>
                                        <p class="mt-1 font-semibold text-slate-950">{{ number_format($dataset->columns_count) }}</p>
                                    </div>
                                    <div class="col-span-2 rounded-2xl bg-white p-3">
                                        <p class="text-xs uppercase tracking-wide text-slate-400">Uploader</p>
                                        <p class="mt-1 font-semibold text-slate-950">{{ $dataset->uploader?->name ?? 'Sin uploader' }}</p>
                                    </div>
                                </div>

                                <div class="mt-4 flex flex-wrap gap-2">
                                    <x-water-button href="{{ route('datasets.show', $dataset) }}" class="border border-slate-200 bg-white text-slate-700">Ver</x-water-button>
                                    <x-water-button href="{{ route('datasets.download', $dataset) }}" class="bg-cyan-500 text-white">Descargar</x-water-button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <div>
                {{ $datasets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
