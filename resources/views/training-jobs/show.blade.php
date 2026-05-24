<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <x-water-chip value="IA / Weka" label="Detalle del entrenamiento" />
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Entrenamiento #{{ $trainingJob->id }}</h2>
                <p class="text-sm text-slate-500">Resultados reales del pipeline ejecutado sobre el dataset asociado.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-water-button href="{{ route('training-jobs.create', ['training_configuration_id' => $trainingJob->training_configuration_id]) }}" class="bg-slate-950 text-white">Reentrenar</x-water-button>
                <x-water-button href="{{ route('training-jobs.index') }}" class="border border-slate-200 bg-white text-slate-700">Volver</x-water-button>
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
                    <p class="text-sm text-slate-500">Estado</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $trainingJob->status->label() }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Accuracy</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ isset($trainingJob->metrics['accuracy']) ? number_format((float) $trainingJob->metrics['accuracy'] * 100, 2) . '%' : 'n/a' }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">F1-score</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ isset($trainingJob->metrics['f1_score']) ? number_format((float) $trainingJob->metrics['f1_score'] * 100, 2) . '%' : 'n/a' }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Tiempo</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ $trainingJob->training_time_ms ? number_format($trainingJob->training_time_ms / 1000, 2).' s' : 'n/a' }}</p>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                <div class="space-y-6">
                    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Modelo y configuración</h3>
                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Dataset</p>
                                <p class="mt-1 font-semibold text-slate-950">{{ $trainingJob->trainingConfiguration?->dataset?->name ?? 'n/a' }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Algoritmo</p>
                                <p class="mt-1 font-semibold text-slate-950">{{ $trainingJob->algorithm->label() }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Target</p>
                                <p class="mt-1 font-semibold text-slate-950">{{ $trainingJob->target_column }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-wide text-slate-400">Modelo guardado</p>
                                <p class="mt-1 break-all font-semibold text-slate-950">{{ $trainingJob->model_path ?? 'n/a' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Matriz de confusión</h3>
                        <div class="mt-4 overflow-x-auto rounded-[24px] border border-slate-200">
                            <table class="min-w-full divide-y divide-slate-200 text-sm">
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($trainingJob->confusion_matrix ?? [] as $row)
                                        <tr>
                                            @foreach ($row as $cell)
                                                <td class="px-4 py-3 text-center text-slate-700">{{ number_format((float) $cell, 2) }}</td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td class="px-4 py-6 text-slate-500">Sin matriz de confusión disponible.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="space-y-6">
                    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Métricas</h3>
                        <div class="mt-5 grid gap-3">
                            @foreach (['accuracy' => 'Accuracy', 'precision' => 'Precision', 'recall' => 'Recall', 'f1_score' => 'F1-score', 'training_time_ms' => 'Tiempo entrenamiento (ms)'] as $key => $label)
                                <div class="flex items-center justify-between rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                                    <span class="text-slate-500">{{ $label }}</span>
                                    <span class="font-semibold text-slate-950">
                                        @if (in_array($key, ['accuracy', 'precision', 'recall', 'f1_score'], true))
                                            {{ isset($trainingJob->metrics[$key]) ? number_format((float) $trainingJob->metrics[$key] * 100, 2) . '%' : 'n/a' }}
                                        @else
                                            {{ $trainingJob->training_time_ms ? number_format($trainingJob->training_time_ms) : 'n/a' }}
                                        @endif
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <h3 class="text-lg font-semibold text-slate-950">Logs de entrenamiento</h3>
                        <pre class="mt-4 max-h-[420px] overflow-auto rounded-[24px] bg-slate-950 p-4 text-xs leading-6 text-slate-100">{{ $trainingJob->execution_log ?? $trainingJob->error_message ?? 'Sin logs disponibles.' }}</pre>
                    </div>
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
