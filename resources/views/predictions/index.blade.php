<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" style="animation: cardIn 0.7s 0.1s cubic-bezier(0.22, 0.68, 0, 1.15) forwards; opacity: 0; transform: translateY(28px);">
            
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-500 mb-1">Módulo Predictivo</p>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 font-['Space_Grotesk']">Predicciones</h2>
                </div>
                <div>
                    <x-water-button href="{{ route('predictions.create') }}">
                        Nueva Predicción
                    </x-water-button>
                </div>
            </div>

            {{-- Historial --}}
            <div class="water-card rounded-[32px] overflow-hidden">
                @if($predictions->isEmpty())
                    <div class="p-12 text-center">
                        <div class="mx-auto w-16 h-16 rounded-full bg-sky-50 flex items-center justify-center text-sky-400 mb-4">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 font-['Space_Grotesk']">Aún no hay predicciones</h3>
                        <p class="mt-2 text-sm text-slate-500 max-w-sm mx-auto">Comienza a predecir la calidad del agua utilizando tus modelos entrenados de Weka.</p>
                        <div class="mt-6">
                            <x-water-button href="{{ route('predictions.create') }}">Realizar primera predicción</x-water-button>
                        </div>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500 bg-sky-50/50 border-b border-sky-100/50">
                                <tr>
                                    <th class="px-6 py-4">ID</th>
                                    <th class="px-6 py-4">Modelo Usado</th>
                                    <th class="px-6 py-4">Fecha</th>
                                    <th class="px-6 py-4">Confianza</th>
                                    <th class="px-6 py-4">Resultado</th>
                                    <th class="px-6 py-4 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($predictions as $prediction)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-4 font-medium text-slate-900">#{{ $prediction->id }}</td>
                                        <td class="px-6 py-4 text-slate-500">
                                            @if($prediction->trainingJob && $prediction->trainingJob->dataset)
                                                {{ $prediction->trainingJob->algorithm->value }} <span class="text-xs text-slate-400">({{ $prediction->trainingJob->dataset->original_name }})</span>
                                            @else
                                                <span class="text-slate-400 italic">Modelo no disponible</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-500">{{ $prediction->created_at->format('d M Y, H:i') }}</td>
                                        <td class="px-6 py-4">
                                            @if($prediction->confidence)
                                                <div class="flex items-center gap-2">
                                                    <div class="w-16 h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                        <div class="h-full bg-sky-400 rounded-full" style="width: {{ $prediction->confidence * 100 }}%"></div>
                                                    </div>
                                                    <span class="text-xs font-semibold text-slate-700">{{ number_format($prediction->confidence * 100, 1) }}%</span>
                                                </div>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if(strtolower($prediction->predicted_class) === 'potable' || $prediction->predicted_class == '1')
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-600 ring-1 ring-emerald-600/20">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Potable
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-600 ring-1 ring-rose-600/20">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span> No Potable
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="{{ route('predictions.show', $prediction) }}" class="text-sky-500 hover:text-sky-700 font-medium transition-colors">Ver detalle &rarr;</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-6 border-t border-slate-100">
                        {{ $predictions->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
