<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Nueva configuración de entrenamiento</h2>
                <p class="mt-1 text-sm text-slate-500">Selecciona un dataset, revisa el preview real y define target, algoritmo y parámetros básicos antes de guardar.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-water-button href="{{ route('training-configurations.index') }}" class="border border-slate-200 bg-white text-slate-700">Ver configuraciones</x-water-button>
                <x-water-button href="{{ route('datasets.index') }}" class="border border-slate-200 bg-white text-slate-700">Ir a datasets</x-water-button>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @php
                $selectedAlgorithm = old('algorithm', 'zeror');
                $initialParameters = old('parameters', $algorithmDefaults[$selectedAlgorithm] ?? $algorithmDefaults['zeror']);
            @endphp

            <div
                x-data="trainingConfigurationBuilder({
                    preview: @js($preview),
                    previewBaseUrl: @js(url('/training-configurations/datasets')),
                    selectedDataset: @js(old('dataset_id', $selectedDataset?->id ?? '')),
                    targetColumn: @js(old('target_column', $preview['suggested_target']['name'] ?? '')),
                    selectedAlgorithm: @js($selectedAlgorithm),
                    parameters: @js($initialParameters),
                    algorithmDefaults: @js($algorithmDefaults),
                })"
                x-init="init()"
                class="space-y-6"
            >
                @if ($errors->any())
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800 shadow-sm">
                        Revisa los campos marcados y corrige los errores antes de continuar.
                    </div>
                @endif

                @if (session('error'))
                    <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800 shadow-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <section class="rounded-[32px] border border-slate-200 bg-white shadow-[0_20px_80px_rgba(15,23,42,0.08)] overflow-hidden">
                    <div class="border-b border-slate-200 bg-slate-50/80 px-6 py-5 sm:px-8">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div class="space-y-2">
                                <x-water-chip value="Weka / IA" label="Configuración de entrenamiento" />
                                <h3 class="text-xl font-semibold tracking-tight text-slate-950">Preparar dataset para entrenamiento</h3>
                                <p class="text-sm text-slate-500">La vista carga el preview real del CSV y deja el target listo para clasificar con ZeroR, OneR, NaiveBayes o Logistic.</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <x-water-chip value="CSV real" label="preview dinámico" />
                                <x-water-chip value="Weka" label="pipeline listo" />
                            </div>
                        </div>
                    </div>

                    <div class="p-6 sm:p-8 space-y-6">
                        <section class="rounded-[28px] border border-slate-200 bg-slate-50 p-6">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-950">Configuración</h4>
                                    <p class="mt-1 text-sm text-slate-500">Selecciona el dataset y define los parámetros base.</p>
                                </div>
                                <x-water-chip :value="count($datasets)" label="datasets" />
                            </div>

                            <form method="POST" action="{{ route('training-configurations.store') }}" class="mt-6 space-y-6">
                                @csrf

                                <div class="grid gap-5 md:grid-cols-2">
                                    <div class="md:col-span-2">
                                        <label for="dataset_id" class="block text-sm font-medium text-slate-700">Dataset</label>
                                        <select
                                            id="dataset_id"
                                            name="dataset_id"
                                            x-model="selectedDataset"
                                            @change="refreshPreview()"
                                            class="mt-2 w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100"
                                        >
                                            <option value="">Selecciona un dataset</option>
                                            @foreach ($datasets as $dataset)
                                                <option value="{{ $dataset->id }}" @selected((string) old('dataset_id', $selectedDataset?->id ?? '') === (string) $dataset->id)>
                                                    {{ $dataset->name }} · {{ $dataset->original_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('dataset_id')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="target_column" class="block text-sm font-medium text-slate-700">Columna objetivo</label>
                                        <select
                                            id="target_column"
                                            name="target_column"
                                            x-model="targetColumn"
                                            class="mt-2 w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100"
                                            :disabled="!preview || !preview.columns"
                                        >
                                            <option value="">Primero selecciona un dataset</option>
                                            <template x-for="column in preview?.columns ?? []" :key="column.name">
                                                <option :value="column.name" x-text="`${column.name} · ${column.type}`"></option>
                                            </template>
                                        </select>
                                        @error('target_column')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div>
                                        <label for="algorithm" class="block text-sm font-medium text-slate-700">Algoritmo</label>
                                        <select
                                            id="algorithm"
                                            name="algorithm"
                                            x-model="selectedAlgorithm"
                                            @change="applyAlgorithmDefaults()"
                                            class="mt-2 w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100"
                                        >
                                            @foreach ($algorithms as $algorithmOption)
                                                <option value="{{ $algorithmOption['value'] }}" @selected($selectedAlgorithm === $algorithmOption['value'])>
                                                    {{ $algorithmOption['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('algorithm')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                    </div>

                                    <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-white p-4">
                                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Parámetros básicos</p>

                                        <div class="mt-4 space-y-4">
                                            <template x-if="selectedAlgorithm === 'zeror'">
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                                                    ZeroR no requiere parámetros adicionales.
                                                </div>
                                            </template>

                                            <template x-if="selectedAlgorithm === 'oner'">
                                                <div class="space-y-3">
                                                    <label for="bucket_size" class="block text-sm font-medium text-slate-700">Tamaño mínimo de bucket</label>
                                                    <input id="bucket_size" type="number" min="1" step="1" x-model="parameters.bucket_size" name="parameters[bucket_size]" class="w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100">
                                                    <p class="text-xs text-slate-500">Controla la granularidad de las reglas construidas por OneR.</p>
                                                    @error('parameters.bucket_size')<p class="text-sm text-rose-600">{{ $message }}</p>@enderror
                                                </div>
                                            </template>

                                            <template x-if="selectedAlgorithm === 'naive_bayes'">
                                                <div class="grid gap-4 md:grid-cols-2">
                                                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-700 shadow-sm">
                                                        <input type="checkbox" x-model="parameters.use_kernel_estimator" name="parameters[use_kernel_estimator]" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                                        <span>Usar kernel estimator</span>
                                                    </label>
                                                    <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-700 shadow-sm">
                                                        <input type="checkbox" x-model="parameters.use_supervised_discretization" name="parameters[use_supervised_discretization]" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500">
                                                        <span>Discretización supervisada</span>
                                                    </label>
                                                </div>
                                            </template>

                                            <template x-if="selectedAlgorithm === 'logistic'">
                                                <div class="grid gap-4 md:grid-cols-2">
                                                    <div>
                                                        <label for="ridge" class="block text-sm font-medium text-slate-700">Ridge</label>
                                                        <input id="ridge" type="number" min="0" step="any" x-model="parameters.ridge" name="parameters[ridge]" class="mt-2 w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100">
                                                    </div>
                                                    <div>
                                                        <label for="max_iterations" class="block text-sm font-medium text-slate-700">Iteraciones máximas</label>
                                                        <input id="max_iterations" type="number" min="1" step="1" x-model="parameters.max_iterations" name="parameters[max_iterations]" class="mt-2 w-full rounded-2xl border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:ring-4 focus:ring-cyan-100">
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-200 pt-5">
                                    <x-water-button href="{{ route('training-configurations.index') }}" class="border border-slate-200 bg-white text-slate-700">Cancelar</x-water-button>
                                    <x-water-button type="submit" class="bg-slate-950 text-white shadow-lg shadow-cyan-900/15">Guardar configuración</x-water-button>
                                </div>
                            </form>
                        </section>

                        <section class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <h4 class="text-lg font-semibold text-slate-950">Preview del dataset</h4>
                                    <p class="mt-1 text-sm text-slate-500">La tabla ocupa todo el ancho disponible y usa scroll interno cuando hay muchas columnas.</p>
                                </div>
                                <div class="chip">
                                    <div class="chip-v" x-text="preview?.statistics?.rows_count ?? 0">0</div>
                                    <div class="chip-l">filas</div>
                                </div>
                            </div>

                            <div x-show="loading" class="mt-5 space-y-3">
                                <div class="h-4 w-1/2 rounded-full bg-slate-200 animate-pulse"></div>
                                <div class="h-28 rounded-3xl bg-slate-100 animate-pulse"></div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div class="h-16 rounded-2xl bg-slate-100 animate-pulse"></div>
                                    <div class="h-16 rounded-2xl bg-slate-100 animate-pulse"></div>
                                </div>
                            </div>

                            <div x-show="previewError" class="mt-5 rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800" x-text="previewError"></div>

                            <div x-show="!loading && !preview" class="mt-5 rounded-3xl border border-dashed border-slate-200 bg-slate-50 p-6 text-sm text-slate-500">
                                Selecciona un dataset para cargar la vista previa real y validar su compatibilidad con Weka.
                            </div>

                            <div x-show="preview" class="mt-5 space-y-5">
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Compatibilidad</p>
                                        <div class="mt-2 flex items-center gap-2">
                                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1" :class="preview?.compatibility?.is_compatible ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-rose-50 text-rose-700 ring-rose-200'" x-text="preview?.compatibility?.is_compatible ? 'Compatible' : 'Requiere revisión'"></span>
                                        </div>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Target sugerido</p>
                                        <p class="mt-2 text-sm font-medium text-slate-950" x-text="preview?.suggested_target?.name ?? 'No detectado'"></p>
                                        <p class="mt-1 text-xs text-slate-500" x-text="preview?.suggested_target?.reason ?? 'Sin sugerencia automática'"></p>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-3 xl:grid-cols-5">
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Filas</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="preview?.statistics?.rows_count ?? 0"></p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Columnas</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="preview?.statistics?.columns_count ?? 0"></p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Missing</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="preview?.statistics?.missing_values ?? 0"></p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Numéricas</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="preview?.statistics?.numeric_columns ?? 0"></p>
                                    </div>
                                    <div class="rounded-2xl bg-slate-50 p-4">
                                        <p class="text-xs uppercase tracking-[0.18em] text-slate-400">Completitud</p>
                                        <p class="mt-2 text-2xl font-semibold text-slate-950" x-text="`${preview?.statistics?.completeness_percentage ?? 0}%`"></p>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h4 class="text-sm font-semibold text-slate-950">Columnas detectadas</h4>
                                            <p class="mt-1 text-xs text-slate-500">Tabla compacta, tipo catálogo.</p>
                                        </div>
                                        <div class="chip">
                                            <div class="chip-v" x-text="preview?.statistics?.columns_count ?? 0">0</div>
                                            <div class="chip-l">columnas</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50">
                                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-xs uppercase tracking-[0.16em] text-slate-500">
                                                <tr>
                                                    <th class="px-4 py-3 text-left">Nombre</th>
                                                    <th class="px-4 py-3 text-left">Tipo</th>
                                                    <th class="px-4 py-3 text-left">Missing</th>
                                                    <th class="px-4 py-3 text-left">Únicos</th>
                                                    <th class="px-4 py-3 text-left">Ejemplos</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                                <template x-for="column in preview?.columns ?? []" :key="column.name">
                                                    <tr class="align-top odd:bg-white even:bg-slate-50/60 hover:bg-cyan-50/40">
                                                        <td class="px-4 py-3 font-medium text-slate-950" x-text="column.name"></td>
                                                        <td class="px-4 py-3">
                                                            <span class="inline-flex rounded-full px-3 py-1 text-[11px] font-semibold uppercase tracking-[0.18em] ring-1" :class="column.type === 'numeric' ? 'bg-cyan-50 text-cyan-700 ring-cyan-200' : column.type === 'categorical' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : column.type === 'boolean' ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-slate-100 text-slate-700 ring-slate-200'" x-text="column.type"></span>
                                                        </td>
                                                        <td class="px-4 py-3 text-slate-600" x-text="`${column.missing_values} (${column.missing_percentage}%)`"></td>
                                                        <td class="px-4 py-3 text-slate-600" x-text="column.distinct_values_count"></td>
                                                        <td class="px-4 py-3 text-slate-600" x-text="(column.sample_values ?? []).join(' · ') || 'n/a'"></td>
                                                    </tr>
                                                </template>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <h4 class="text-sm font-semibold text-slate-950">Primeras filas</h4>
                                            <p class="mt-1 text-xs text-slate-500">Tabla horizontal con header fijo y scroll interno.</p>
                                        </div>
                                        <div class="chip">
                                            <div class="chip-v" x-text="preview?.preview_rows_count ?? 0">0</div>
                                            <div class="chip-l">filas visibles</div>
                                        </div>
                                    </div>

                                    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-200 bg-slate-50">
                                        <div class="max-h-[360px] overflow-auto">
                                            <table class="min-w-max w-full divide-y divide-slate-200 text-sm">
                                                <thead class="sticky top-0 z-10 bg-slate-50 text-xs uppercase tracking-[0.16em] text-slate-500 shadow-[0_1px_0_rgba(148,163,184,0.35)]">
                                                    <tr>
                                                        <template x-for="column in preview?.columns ?? []" :key="column.name">
                                                            <th class="whitespace-nowrap px-4 py-3 text-left" x-text="column.name"></th>
                                                        </template>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-slate-100 bg-white">
                                                    <template x-for="(row, rowIndex) in preview?.preview_rows ?? []" :key="rowIndex">
                                                        <tr class="align-top odd:bg-white even:bg-slate-50/60 hover:bg-cyan-50/40">
                                                            <template x-for="column in preview?.columns ?? []" :key="column.name">
                                                                <td class="whitespace-nowrap px-4 py-3 text-slate-600" x-text="row[column.name] ?? '—'"></td>
                                                            </template>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div x-show="preview?.compatibility?.warnings?.length" class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-800">
                                    <p class="font-semibold">Advertencias</p>
                                    <ul class="mt-2 space-y-1 list-disc pl-5">
                                        <template x-for="(warning, warningIndex) in preview?.compatibility?.warnings ?? []" :key="warningIndex">
                                            <li x-text="warning"></li>
                                        </template>
                                    </ul>
                                </div>

                                <div x-show="preview?.compatibility?.issues?.length" class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800">
                                    <p class="font-semibold">Bloqueos de compatibilidad</p>
                                    <ul class="mt-2 space-y-1 list-disc pl-5">
                                        <template x-for="(issue, issueIndex) in preview?.compatibility?.issues ?? []" :key="issueIndex">
                                            <li x-text="issue"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </section>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <script>
        function trainingConfigurationBuilder(state) {
            return {
                preview: state.preview,
                previewBaseUrl: state.previewBaseUrl,
                selectedDataset: state.selectedDataset ? String(state.selectedDataset) : '',
                targetColumn: state.targetColumn || '',
                selectedAlgorithm: state.selectedAlgorithm || 'zeror',
                parameters: state.parameters || {},
                algorithmDefaults: state.algorithmDefaults || {},
                loading: false,
                previewError: null,

                init() {
                    this.applyAlgorithmDefaults();

                    if (! this.targetColumn && this.preview?.suggested_target?.name) {
                        this.targetColumn = this.preview.suggested_target.name;
                    }
                },

                applyAlgorithmDefaults() {
                    const defaults = this.algorithmDefaults[this.selectedAlgorithm] || {};

                    Object.entries(defaults).forEach(([key, value]) => {
                        if (this.parameters[key] === undefined || this.parameters[key] === null || this.parameters[key] === '') {
                            this.parameters[key] = value;
                        }
                    });
                },

                async refreshPreview() {
                    if (! this.selectedDataset) {
                        this.preview = null;
                        this.targetColumn = '';
                        return;
                    }

                    this.loading = true;
                    this.previewError = null;

                    try {
                        const response = await fetch(`${this.previewBaseUrl}/${this.selectedDataset}/preview?limit=10`, {
                            headers: {
                                Accept: 'application/json',
                            },
                        });

                        if (! response.ok) {
                            throw new Error('No se pudo cargar el preview del dataset.');
                        }

                        this.preview = await response.json();

                        if (this.preview?.suggested_target?.name) {
                            this.targetColumn = this.preview.suggested_target.name;
                        } else if (Array.isArray(this.preview?.columns) && this.preview.columns.length > 0 && ! this.targetColumn) {
                            this.targetColumn = this.preview.columns[0].name;
                        }
                    } catch (error) {
                        this.preview = null;
                        this.previewError = error.message ?? 'No se pudo cargar el preview del dataset.';
                    } finally {
                        this.loading = false;
                    }
                },
            };
        }
    </script>
</x-app-layout>