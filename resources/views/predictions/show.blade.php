<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" style="animation: cardIn 0.7s 0.1s cubic-bezier(0.22, 0.68, 0, 1.15) forwards; opacity: 0; transform: translateY(28px);">
            
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <a href="{{ route('predictions.index') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-500 shadow-sm border border-slate-100 hover:bg-slate-50 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    </a>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-500 mb-1">Resultado de Análisis</p>
                        <h2 class="text-3xl font-semibold tracking-tight text-slate-900 font-['Space_Grotesk']">Predicción #{{ $prediction->id }}</h2>
                    </div>
                </div>
                <div class="text-right text-xs text-slate-500">
                    <p>Ejecutado: {{ $prediction->created_at->format('d/m/Y H:i') }}</p>
                    <p>Tiempo de procesamiento: {{ $prediction->execution_time_ms ?? 'N/A' }} ms</p>
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Resultado Card --}}
                <div class="water-card rounded-[32px] p-8 text-center flex flex-col items-center justify-center border-t-4 {{ (strtolower($prediction->predicted_class) === 'potable' || $prediction->predicted_class == '1') ? 'border-emerald-400' : 'border-rose-400' }}">
                    <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-6">Diagnóstico de Potabilidad</p>
                    
                    @if(strtolower($prediction->predicted_class) === 'potable' || $prediction->predicted_class == '1')
                        <div class="w-24 h-24 rounded-full bg-emerald-50 flex items-center justify-center mb-6 ring-8 ring-emerald-50/50">
                            <svg class="w-12 h-12 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-4xl font-bold text-slate-900 font-['Space_Grotesk'] mb-2">Potable</h3>
                        <p class="text-emerald-600 text-sm font-medium">El agua es apta para consumo.</p>
                    @else
                        <div class="w-24 h-24 rounded-full bg-rose-50 flex items-center justify-center mb-6 ring-8 ring-rose-50/50">
                            <svg class="w-12 h-12 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <h3 class="text-4xl font-bold text-slate-900 font-['Space_Grotesk'] mb-2">No Potable</h3>
                        <p class="text-rose-600 text-sm font-medium">El agua NO es segura para consumo.</p>
                    @endif

                    @if($prediction->confidence)
                        <div class="mt-8 w-full">
                            <div class="flex justify-between text-xs font-semibold mb-2">
                                <span class="text-slate-500">Confianza del modelo</span>
                                <span class="text-slate-900">{{ number_format($prediction->confidence * 100, 1) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full {{ (strtolower($prediction->predicted_class) === 'potable' || $prediction->predicted_class == '1') ? 'bg-emerald-400' : 'bg-rose-400' }}" style="width: {{ $prediction->confidence * 100 }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Detalles Card --}}
                <div class="space-y-6">
                    <div class="water-panel rounded-[24px] p-6">
                        <h4 class="text-xs font-bold uppercase tracking-[0.1em] text-sky-500 mb-4">Información del Modelo</h4>
                        @if($prediction->trainingJob)
                            <div class="space-y-3">
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">ID Entrenamiento:</span>
                                    <span class="font-medium text-slate-900">#{{ $prediction->trainingJob->id }}</span>
                                </div>
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Algoritmo:</span>
                                    <span class="font-medium text-slate-900">{{ $prediction->trainingJob->algorithm->value }}</span>
                                </div>
                                @if($prediction->trainingJob->dataset)
                                    <div class="flex justify-between text-sm">
                                        <span class="text-slate-500">Dataset original:</span>
                                        <span class="font-medium text-slate-900">{{ $prediction->trainingJob->dataset->original_name }}</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-sm">
                                    <span class="text-slate-500">Accuracy Entrenamiento:</span>
                                    <span class="font-medium text-slate-900">{{ number_format(data_get($prediction->trainingJob->metrics, 'accuracy', 0) * 100, 1) }}%</span>
                                </div>
                            </div>
                        @else
                            <p class="text-sm text-slate-500 italic">El modelo utilizado ya no se encuentra disponible.</p>
                        @endif
                    </div>

                    <div class="water-panel rounded-[24px] p-6">
                        <h4 class="text-xs font-bold uppercase tracking-[0.1em] text-sky-500 mb-4">Parámetros Analizados</h4>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                            @foreach($prediction->input_data as $key => $value)
                                @php
                                    if (is_array($value) || is_object($value)) {
                                        $displayValue = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                                    } elseif ($value === null) {
                                        $displayValue = '-';
                                    } else {
                                        $displayValue = (string) $value;
                                    }
                                @endphp
                                <div class="bg-slate-50/50 p-2 rounded-xl">
                                    <span class="block text-[10px] font-semibold uppercase text-slate-400">{{ str_replace('_', ' ', $key) }}</span>
                                    <span class="block text-sm font-medium text-slate-900">{{ $displayValue }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
