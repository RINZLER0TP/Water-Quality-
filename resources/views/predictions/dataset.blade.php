<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" style="animation: cardIn 0.7s 0.1s cubic-bezier(0.22, 0.68, 0, 1.15) forwards; opacity: 0; transform: translateY(28px);">

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('predictions.create') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-500 shadow-sm border border-slate-100 hover:bg-slate-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-500 mb-1">Predicción por Dataset</p>
                        <h2 class="text-3xl font-semibold tracking-tight text-slate-900 font-['Space_Grotesk']">Resultado del dataset</h2>
                    </div>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p>Modelo: #{{ $batchModelId ?? '-' }}</p>
                    <p>{{ $batchDatasetName ?? 'Dataset asociado' }}</p>
                </div>
            </div>

            @if(session('success'))
                <div class="rounded-2xl bg-emerald-50 p-4 border border-emerald-100 shadow-sm flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            @endif

            @if(session('error'))
                <div class="rounded-2xl bg-rose-50 p-4 border border-rose-100 shadow-sm flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-rose-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <p class="text-sm font-medium text-rose-800">{{ session('error') }}</p>
                </div>
            @endif

            @if(isset($batchResult))
                @php
                    $batchPotable = (int) ($batchResult['summary']['potable'] ?? 0);
                    $batchNonPotable = (int) ($batchResult['summary']['non_potable'] ?? 0);
                    $batchTotal = max((int) ($batchResult['total'] ?? 0), 1);
                    $batchIsPotable = $batchPotable >= $batchNonPotable;
                    $batchConfidence = round((max($batchPotable, $batchNonPotable) / $batchTotal) * 100, 1);
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="water-card rounded-[32px] p-8 text-center flex flex-col items-center justify-center border-t-4 {{ $batchIsPotable ? 'border-emerald-400' : 'border-rose-400' }}">
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-6">Diagnóstico del Dataset</p>

                        @if($batchIsPotable)
                            <div class="w-24 h-24 rounded-full bg-emerald-50 flex items-center justify-center mb-6 ring-8 ring-emerald-50/50">
                                <svg class="w-12 h-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <h3 class="text-4xl font-bold text-slate-900 font-['Space_Grotesk'] mb-2">Potable</h3>
                            <p class="text-emerald-600 text-sm font-medium">La mayoría de filas del dataset fueron clasificadas como aptas para consumo.</p>
                        @else
                            <div class="w-24 h-24 rounded-full bg-rose-50 flex items-center justify-center mb-6 ring-8 ring-rose-50/50">
                                <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <h3 class="text-4xl font-bold text-slate-900 font-['Space_Grotesk'] mb-2">No Potable</h3>
                            <p class="text-rose-600 text-sm font-medium">La mayoría de filas del dataset no es segura para consumo.</p>
                        @endif

                        <div class="mt-8 w-full">
                            <div class="flex justify-between text-xs font-semibold mb-2">
                                <span class="text-slate-500">Confianza del resultado</span>
                                <span class="text-slate-900">{{ $batchConfidence }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ $batchIsPotable ? 'bg-emerald-400' : 'bg-rose-400' }}" style="width: {{ $batchConfidence }}%"></div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div class="water-panel rounded-[24px] p-6">
                            <h4 class="text-xs font-bold uppercase tracking-[0.1em] text-sky-500 mb-4">Información del Modelo</h4>
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">ID Entrenamiento:</span>
                                    <span class="font-medium text-slate-900">#{{ $batchModelId ?? '-' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Algoritmo:</span>
                                    <span class="font-medium text-slate-900">{{ $batchAlgorithm ?? 'N/D' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Dataset original:</span>
                                    <span class="font-medium text-slate-900">{{ $batchDatasetName ?? 'Dataset asociado' }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Filas analizadas:</span>
                                    <span class="font-medium text-slate-900">{{ $batchResult['total'] ?? 0 }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Tiempo de procesamiento:</span>
                                    <span class="font-medium text-slate-900">{{ number_format(($batchResult['execution_time_ms'] ?? 0) / 1000, 2) }}s</span>
                                </div>
                            </div>
                        </div>

                        <div class="water-panel rounded-[24px] p-6">
                            <h4 class="text-xs font-bold uppercase tracking-[0.1em] text-sky-500 mb-4">Resumen del Dataset</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="bg-slate-50/50 p-4 rounded-2xl text-center">
                                        <span class="block text-[10px] font-semibold uppercase text-slate-400">Sí potable</span>
                                        <div class="mt-2">
                                            <span class="text-2xl font-bold text-emerald-600">{{ $batchPotable }}</span>
                                            <span class="ml-2 text-sm text-slate-400">({{ round(($batchPotable / max($batchTotal,1)) * 100, 1) }}%)</span>
                                        </div>
                                    </div>
                                    <div class="bg-slate-50/50 p-4 rounded-2xl text-center">
                                        <span class="block text-[10px] font-semibold uppercase text-slate-400">No potable</span>
                                        <div class="mt-2">
                                            <span class="text-2xl font-bold text-rose-600">{{ $batchNonPotable }}</span>
                                            <span class="ml-2 text-sm text-slate-400">({{ round(($batchNonPotable / max($batchTotal,1)) * 100, 1) }}%)</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="water-panel rounded-[32px] p-8 text-center">
                    <p class="text-sm text-slate-500">No hay resultados todavía. Elige un modelo y ejecuta la predicción del dataset para ver el diagnóstico aquí.</p>
                </div>
            @endif

            @unless(isset($batchResult))
            <div class="water-panel rounded-[32px] p-8">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between mb-6">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-emerald-500 mb-1">Ejecutar predicción del dataset</p>
                        <h3 class="text-2xl font-semibold tracking-tight text-slate-900 font-['Space_Grotesk']">Selecciona el modelo entrenado</h3>
                        <p class="mt-1 text-sm text-slate-500">Esta vista queda dedicada solo al dataset cargado.</p>
                    </div>
                </div>

                <form action="{{ route('predictions.dataset.store') }}" method="POST" class="space-y-6">
                    @csrf

                    <div>
                        <label for="dataset_training_job_id" class="block text-sm font-semibold text-slate-900 mb-2">Modelo entrenado</label>
                        <select name="training_job_id" id="dataset_training_job_id" required class="mt-1 block w-full rounded-2xl border-slate-200 focus:border-emerald-500 focus:ring-emerald-500 sm:text-sm p-3 shadow-sm">
                            <option value="" disabled selected>Elige uno...</option>
                            @foreach($activeModels as $model)
                                <option value="{{ $model->id }}">
                                    #{{ $model->id }} - {{ $model->algorithm->value }} · Dataset: {{ $model->trainingConfiguration?->dataset?->name ?? 'Sin nombre' }} · Accuracy: {{ number_format(data_get($model->metrics, 'accuracy', 0) * 100, 1) }}%
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-800">
                        Al ejecutar, el sistema tomará el dataset asociado al modelo y mostrará el resultado en esta misma página.
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('predictions.create') }}" class="inline-flex items-center px-6 py-3 border border-slate-200 rounded-full shadow-sm text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 focus:outline-none transition-colors">
                            Ir a personalizada
                        </a>
                        <button type="submit" @if($activeModels->isEmpty()) disabled @endif class="inline-flex justify-center items-center px-8 py-3 border border-transparent text-sm font-semibold rounded-full shadow-lg text-white bg-gradient-to-r from-emerald-500 to-teal-400 hover:from-emerald-600 hover:to-teal-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                            Ejecutar predicción del dataset &rarr;
                        </button>
                    </div>
                </form>
            </div>
            @endunless
        </div>
    </div>
</x-app-layout>