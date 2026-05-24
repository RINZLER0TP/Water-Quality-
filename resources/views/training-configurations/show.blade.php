<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="space-y-2">
                <x-water-chip value="{{ $trainingConfiguration->algorithm->label() }}" label="Algoritmo guardado" />
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Configuración de entrenamiento #{{ $trainingConfiguration->id }}</h2>
                <p class="text-sm text-slate-500">Target, parámetros y snapshot analítico persistidos para pasar a la etapa automática.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-water-button href="{{ route('training-configurations.index') }}" class="border border-slate-200 bg-white text-slate-700">Volver</x-water-button>
                <x-water-button href="{{ route('training-configurations.create', ['dataset_id' => $trainingConfiguration->dataset_id]) }}" class="bg-slate-950 text-white">Nueva desde este dataset</x-water-button>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @php
                $parameterSchema = collect($trainingConfiguration->algorithm?->parameterSchema() ?? [])->keyBy('name');
                $parameterCount = count($trainingConfiguration->parameters ?? []);
            @endphp

            @if (session('status'))
                <div class="rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-4 text-sm text-emerald-800 shadow-sm">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Dataset</p>
                    <p class="mt-2 text-xl font-semibold text-slate-950">{{ $trainingConfiguration->dataset?->name ?? 'n/a' }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $trainingConfiguration->dataset?->original_name ?? '' }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Target</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $trainingConfiguration->target_column }}</p>
                    <p class="mt-2 text-xs text-slate-500">Columna objetivo de clasificación.</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Algoritmo</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $trainingConfiguration->algorithm->label() }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $trainingConfiguration->algorithm->description() }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Creador</p>
                    <p class="mt-2 text-xl font-semibold text-slate-950">{{ $trainingConfiguration->creator?->name ?? 'n/a' }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $trainingConfiguration->created_at?->format('d/m/Y H:i') }}</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.15fr_0.85fr]">
                <div class="space-y-6">
                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <div class="mx-auto max-w-2xl text-center">
                            <div class="flex justify-center">
                                <x-water-chip value="{{ $parameterCount }}" label="{{ $parameterCount === 1 ? 'campo' : 'campos' }}" />
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-slate-950">Parámetros básicos</h3>
                            <p class="mt-1 text-sm text-slate-500">Resumen centrado de la configuración usada para el entrenamiento.</p>
                        </div>

                        <div class="mt-6 grid gap-4 sm:grid-cols-2 justify-items-stretch">
                            @forelse (($trainingConfiguration->parameters ?? []) as $key => $value)
                                @php
                                    $schema = $parameterSchema->get($key, []);
                                    $label = $schema['label'] ?? str($key)->replace('_', ' ')->title();
                                    $isBoolean = ($schema['type'] ?? null) === 'boolean';
                                    $formattedValue = $isBoolean
                                        ? (($value === true || $value === 1 || $value === '1' || $value === 'true') ? 'Sí' : 'No')
                                        : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value);
                                @endphp
                                <div class="rounded-2xl bg-slate-50 p-4 text-center">
                                    <p class="text-xs uppercase tracking-[0.18em] text-slate-400">{{ $label }}</p>
                                    <p class="mt-2 text-sm font-medium text-slate-950">{{ $formattedValue }}</p>
                                </div>
                            @empty
                                <div class="sm:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600 text-center">Este algoritmo no requiere parámetros adicionales.</div>
                            @endforelse
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <div class="mx-auto max-w-3xl text-center">
                            <div class="flex justify-center">
                                <x-water-chip value="{{ $trainingConfiguration->analysis['statistics']['rows_count'] ?? 0 }}" label="filas" />
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-slate-950">Preview persistido</h3>
                            <p class="mt-1 text-sm text-slate-500">Snapshot analítico guardado para poder repetir y auditar el entrenamiento.</p>
                        </div>

                        <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                            <div class="rounded-2xl bg-slate-50 p-3 flex flex-col justify-center items-center text-center">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Filas</p>
                                <p class="mt-1 text-xl sm:text-2xl font-semibold text-slate-950">{{ $trainingConfiguration->analysis['statistics']['rows_count'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 flex flex-col justify-center items-center text-center">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Columnas</p>
                                <p class="mt-1 text-xl sm:text-2xl font-semibold text-slate-950">{{ $trainingConfiguration->analysis['statistics']['columns_count'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 flex flex-col justify-center items-center text-center">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Missing</p>
                                <p class="mt-1 text-xl sm:text-2xl font-semibold text-slate-950">{{ $trainingConfiguration->analysis['statistics']['missing_values'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 flex flex-col justify-center items-center text-center">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Numéricas</p>
                                <p class="mt-1 text-xl sm:text-2xl font-semibold text-slate-950">{{ $trainingConfiguration->analysis['statistics']['numeric_columns'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-2xl bg-slate-50 p-3 flex flex-col justify-center items-center text-center sm:col-span-3 lg:col-span-1">
                                <p class="text-[10px] uppercase tracking-wider text-slate-400">Completo</p>
                                <p class="mt-1 text-xl sm:text-2xl font-semibold text-slate-950">{{ $trainingConfiguration->analysis['statistics']['completeness_percentage'] ?? 0 }}%</p>
                            </div>
                        </div>

                        <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 text-center">
                            <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Target sugerido</p>
                            <p class="mt-2 text-sm font-medium text-slate-950">{{ $trainingConfiguration->analysis['suggested_target']['name'] ?? 'n/a' }}</p>
                            <p class="mt-1 text-xs text-slate-500">{{ $trainingConfiguration->analysis['suggested_target']['reason'] ?? 'Sin sugerencia automática' }}</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Compatibilidad Weka</h3>
                        <div class="mt-4 flex items-center gap-2">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 {{ ($trainingConfiguration->analysis['compatibility']['is_compatible'] ?? false) ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200' }}">
                                {{ ($trainingConfiguration->analysis['compatibility']['is_compatible'] ?? false) ? 'Compatible' : 'Requiere revisión' }}
                            </span>
                        </div>

                        @if (! empty($trainingConfiguration->analysis['compatibility']['warnings'] ?? []))
                            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                                <p class="font-semibold">Advertencias</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($trainingConfiguration->analysis['compatibility']['warnings'] as $warning)
                                        <li>{{ $warning }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (! empty($trainingConfiguration->analysis['compatibility']['issues'] ?? []))
                            <div class="mt-4 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800">
                                <p class="font-semibold">Bloqueos</p>
                                <ul class="mt-2 list-disc space-y-1 pl-5">
                                    @foreach ($trainingConfiguration->analysis['compatibility']['issues'] as $issue)
                                        <li>{{ $issue }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-slate-950 p-6 text-white shadow-[0_20px_80px_rgba(15,23,42,0.22)]">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm text-cyan-200/80">Preparado para Java + Weka</p>
                                <h3 class="mt-1 text-lg font-semibold">Snapshot persistido para entrenamiento automático</h3>
                            </div>
                            <x-water-chip value="analysis" label="snapshot" />
                        </div>
                        <p class="mt-4 text-sm leading-6 text-slate-300">Esta configuración almacena el análisis del dataset, el target seleccionado y los parámetros base para que el proceso de entrenamiento pueda ejecutarse de forma reproducible.</p>
                        <div class="mt-5 flex flex-wrap gap-2">
                            <x-water-button href="{{ route('training-configurations.create', ['dataset_id' => $trainingConfiguration->dataset_id]) }}" class="bg-cyan-400 text-slate-950">Duplicar configuración</x-water-button>
                            <x-water-button href="{{ route('training-configurations.index') }}" class="border border-white/15 bg-white/5 text-white">Volver</x-water-button>
                        </div>
                    </div>

                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Raw analysis</h3>
                        <pre class="mt-4 overflow-x-auto rounded-2xl bg-slate-950 p-4 text-xs leading-6 text-slate-100">{{ json_encode($trainingConfiguration->analysis ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>