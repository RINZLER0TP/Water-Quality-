<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <x-water-chip value="{{ $dataset->status->label() }}" label="Estado del dataset" />
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">{{ $dataset->name }}</h2>
                <p class="text-sm text-slate-500">{{ $dataset->original_name }} · {{ number_format($dataset->rows_count) }} filas · {{ number_format($dataset->columns_count) }} columnas</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-water-button href="{{ route('datasets.download', $dataset) }}" class="bg-cyan-500 text-white shadow-lg shadow-cyan-500/20">Descargar</x-water-button>
                <x-water-button
                    type="button"
                    x-data=""
                    x-on:click.prevent="$dispatch('open-modal', 'confirm-dataset-deletion')"
                    class="bg-rose-600 text-white shadow-lg shadow-rose-900/10"
                >
                    Eliminar
                </x-water-button>
                <x-water-button href="{{ route('datasets.index') }}" class="border border-slate-200 bg-white text-slate-700">Volver</x-water-button>
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
                    <p class="text-sm text-slate-500">Filas</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($dataset->rows_count) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Datos listos para entrenamiento.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Columnas</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($dataset->columns_count) }}</p>
                    <p class="mt-2 text-xs text-slate-500">Variables detectadas automáticamente.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Tamaño</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($dataset->file_size / 1024 / 1024, 2) }} MB</p>
                    <p class="mt-2 text-xs text-slate-500">Archivo persistido en local storage.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Uploader</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $dataset->uploader?->name ?? 'Sin dato' }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $dataset->created_at?->format('d/m/Y H:i') }}</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-6">
                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Metadata del dataset</h3>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Nombre</p>
                                <p class="mt-2 text-sm font-medium text-slate-950">{{ $dataset->name }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Nombre original</p>
                                <p class="mt-2 text-sm font-medium text-slate-950">{{ $dataset->original_name }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Estado</p>
                                <p class="mt-2 text-sm font-medium text-slate-950">{{ $dataset->status->label() }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-4">
                                <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Ruta</p>
                                <p class="mt-2 break-all text-sm font-medium text-slate-950">{{ $dataset->file_path }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <div class="flex items-center justify-between gap-3">
                            <h3 class="text-lg font-semibold text-slate-950">Headers detectados</h3>
                            <x-water-chip value="{{ count($dataset->metadata['headers'] ?? []) }}" label="Columnas" />
                        </div>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (($dataset->metadata['headers'] ?? []) as $header)
                                <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-sm text-slate-700">{{ $header }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Metadata técnica</h3>
                        <div class="mt-4 space-y-3 text-sm text-slate-600">
                            <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                                <span>Delimiter</span>
                                <span class="font-medium text-slate-950">{{ $dataset->metadata['delimiter'] ?? 'n/a' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                                <span>Validado en</span>
                                <span class="font-medium text-slate-950">{{ $dataset->metadata['validated_at'] ?? 'n/a' }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-4 rounded-2xl bg-slate-50 px-4 py-3">
                                <span>Uploader</span>
                                <span class="font-medium text-slate-950">{{ $dataset->uploader?->name ?? 'n/a' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-slate-950 p-6 text-white shadow-[0_20px_80px_rgba(15,23,42,0.22)]">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm text-cyan-200/80">Storage seguro</p>
                                <h3 class="mt-1 text-lg font-semibold">Archivo listo para consumo automático</h3>
                            </div>
                            <x-water-chip value="safe" label="download" />
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-300">La descarga verifica existencia en disco local y la eliminación borra primero el archivo y luego el registro, evitando residuos en storage.</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <x-water-button href="{{ route('datasets.download', $dataset) }}" class="bg-cyan-400 text-slate-950">Descargar</x-water-button>
                            <x-water-button href="{{ route('datasets.index') }}" class="border border-white/15 bg-white/5 text-white">Volver</x-water-button>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Raw metadata</h3>
                        <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100">{{ json_encode($dataset->metadata ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <x-modal name="confirm-dataset-deletion" maxWidth="md">
        <form method="POST" action="{{ route('datasets.destroy', $dataset) }}" class="overflow-hidden rounded-[28px] bg-white shadow-[0_30px_100px_rgba(15,23,42,0.18)] ring-1 ring-slate-200">
            @csrf
            @method('DELETE')

            <div class="border-b border-slate-200 bg-gradient-to-br from-rose-50 via-white to-slate-50 px-6 py-6 sm:px-8">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-rose-100 text-rose-600 ring-1 ring-rose-200">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                            <path d="M12 9v4" />
                            <path d="M12 17h.01" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <p class="text-[0.72rem] font-semibold uppercase tracking-[0.28em] text-rose-500">Confirmación crítica</p>
                        <h2 class="max-w-xl text-2xl font-semibold tracking-tight text-slate-950">¿Seguro que quieres eliminar este dataset?</h2>
                        <p class="max-w-2xl text-sm leading-6 text-slate-600">
                            Esta acción borrará el archivo almacenado y el registro del catálogo. Si continúas, perderás el acceso al dataset para descarga y futuras comparaciones.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-5 px-6 py-6 sm:px-8 sm:py-7">
                <div class="rounded-2xl bg-slate-50 px-4 py-4 text-sm text-slate-600 ring-1 ring-slate-200">
                    <div class="grid gap-2 sm:grid-cols-3 sm:gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Dataset</p>
                            <p class="mt-1 truncate font-medium text-slate-950">{{ $dataset->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Filas</p>
                            <p class="mt-1 font-medium text-slate-950">{{ number_format($dataset->rows_count) }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Archivo</p>
                            <p class="mt-1 truncate font-medium text-slate-950">{{ $dataset->original_name }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    Revisa bien antes de continuar. Esta acción no se puede deshacer.
                </div>

                <div class="flex flex-wrap justify-end gap-3 border-t border-slate-100 pt-2">
                    <x-water-button type="button" x-on:click="$dispatch('close')" class="border border-slate-200 bg-white text-slate-700 shadow-sm hover:bg-slate-50">
                        Cancelar
                    </x-water-button>
                    <x-water-button type="submit" class="bg-rose-600 text-white shadow-lg shadow-rose-900/20 hover:bg-rose-500">
                        Sí, eliminar dataset
                    </x-water-button>
                </div>
            </div>
        </form>
    </x-modal>
</x-app-layout>
