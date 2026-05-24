<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" style="animation: cardIn 0.7s 0.1s cubic-bezier(0.22, 0.68, 0, 1.15) forwards; opacity: 0; transform: translateY(28px);">
            
            <div class="flex items-center gap-4">
                <a href="{{ route('predictions.index') }}" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-slate-500 shadow-sm border border-slate-100 hover:bg-slate-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                </a>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-500 mb-1">Nuevo Análisis</p>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 font-['Space_Grotesk']">Ejecutar Predicción</h2>
                </div>
            </div>

            <form action="{{ route('predictions.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="water-panel rounded-[32px] p-8">
                    <div class="mb-6 pb-6 border-b border-sky-100">
                        <label for="training_job_id" class="block text-sm font-semibold text-slate-900 mb-2">Seleccionar Modelo Entrenado</label>
                        @if($activeModels->isEmpty())
                            <div class="p-4 rounded-2xl bg-amber-50 border border-amber-100 text-amber-800 text-sm">
                                No tienes modelos Weka entrenados activos. <a href="{{ route('training-jobs.create') }}" class="font-bold underline hover:text-amber-900">Entrena uno aquí</a>.
                            </div>
                        @else
                            <select name="training_job_id" id="training_job_id" required class="mt-1 block w-full rounded-2xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 sm:text-sm p-3 shadow-sm">
                                <option value="" disabled selected>Elige un modelo...</option>
                                @foreach($activeModels as $model)
                                    <option value="{{ $model->id }}" {{ old('training_job_id') == $model->id ? 'selected' : '' }}>
                                        #{{ $model->id }} - {{ $model->algorithm->value }} (Accuracy: {{ number_format(data_get($model->metrics, 'accuracy', 0) * 100, 1) }}%)
                                    </option>
                                @endforeach
                            </select>
                        @endif
                        @error('training_job_id') <p class="mt-2 text-sm text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <h3 class="text-lg font-semibold text-slate-900 font-['Space_Grotesk'] mb-6">Parámetros del Agua</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @php
                                $params = [
                                    ['name' => 'ph', 'label' => 'pH', 'placeholder' => 'Ej: 7.0', 'step' => '0.01'],
                                    ['name' => 'hardness', 'label' => 'Dureza', 'placeholder' => 'Ej: 204.8', 'step' => '0.01'],
                                    ['name' => 'solids', 'label' => 'Sólidos', 'placeholder' => 'Ej: 20791.3', 'step' => '0.01'],
                                    ['name' => 'chloramines', 'label' => 'Cloraminas', 'placeholder' => 'Ej: 7.3', 'step' => '0.01'],
                                    ['name' => 'sulfate', 'label' => 'Sulfato', 'placeholder' => 'Ej: 368.5', 'step' => '0.01'],
                                    ['name' => 'conductivity', 'label' => 'Conductividad', 'placeholder' => 'Ej: 564.3', 'step' => '0.01'],
                                    ['name' => 'organic_carbon', 'label' => 'Carbono Orgánico', 'placeholder' => 'Ej: 10.3', 'step' => '0.01'],
                                    ['name' => 'trihalomethanes', 'label' => 'Trihalometanos', 'placeholder' => 'Ej: 86.9', 'step' => '0.01'],
                                    ['name' => 'turbidity', 'label' => 'Turbidez', 'placeholder' => 'Ej: 2.9', 'step' => '0.01'],
                                ];
                            @endphp

                            @foreach($params as $param)
                                <div>
                                    <label for="{{ $param['name'] }}" class="block text-[11px] font-bold uppercase tracking-[0.1em] text-slate-500">{{ $param['label'] }}</label>
                                    <input type="number" step="{{ $param['step'] }}" name="{{ $param['name'] }}" id="{{ $param['name'] }}" value="{{ old($param['name']) }}" placeholder="{{ $param['placeholder'] }}" required class="mt-2 block w-full rounded-2xl border-slate-200 focus:border-sky-500 focus:ring-sky-500 sm:text-sm p-3 shadow-sm bg-slate-50/50 hover:bg-white transition-colors">
                                    @error($param['name']) <p class="mt-1 text-xs text-rose-500">{{ $message }}</p> @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('predictions.index') }}" class="inline-flex items-center px-6 py-3 border border-slate-200 rounded-full shadow-sm text-sm font-semibold text-slate-600 bg-white hover:bg-slate-50 focus:outline-none transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" @if($activeModels->isEmpty()) disabled @endif class="inline-flex justify-center items-center px-8 py-3 border border-transparent text-sm font-semibold rounded-full shadow-lg text-white bg-gradient-to-r from-sky-500 to-cyan-400 hover:from-sky-600 hover:to-cyan-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-500 transition-all hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed">
                        Ejecutar Modelo Weka &rarr;
                    </button>
                </div>
            </form>

        </div>
    </div>
</x-app-layout>
