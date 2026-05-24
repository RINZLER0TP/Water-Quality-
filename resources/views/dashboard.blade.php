<x-app-layout>
    <div class="py-8 sm:py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" style="animation: cardIn 0.7s 0.1s cubic-bezier(0.22, 0.68, 0, 1.15) forwards; opacity: 0; transform: translateY(28px);">
            
            {{-- Encabezado del Dashboard --}}
            <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-sky-500 mb-1">Visión General</p>
                    <h2 class="text-3xl font-semibold tracking-tight text-slate-900 font-['Space_Grotesk']">Panel de control</h2>
                </div>
                <div class="flex items-center gap-2 text-sm text-slate-500 bg-white/60 px-4 py-2 rounded-full border border-slate-200/60 shadow-sm backdrop-blur-md">
                    <span class="relative flex h-2.5 w-2.5">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    Sistema en línea
                </div>
            </div>

            {{-- Acciones Rápidas (Command Center) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ route('datasets.create') }}" class="group relative overflow-hidden rounded-[24px] bg-white p-6 shadow-sm ring-1 ring-slate-200 hover:ring-sky-300 hover:shadow-md transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-50 to-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-600 group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900">Subir Dataset</h3>
                            <p class="text-xs text-slate-500">Cargar nuevo archivo CSV</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('training-jobs.create') }}" class="group relative overflow-hidden rounded-[24px] bg-white p-6 shadow-sm ring-1 ring-slate-200 hover:ring-indigo-300 hover:shadow-md transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-white opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-600 group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-900">Entrenar Modelo</h3>
                            <p class="text-xs text-slate-500">Crear IA con Weka</p>
                        </div>
                    </div>
                </a>

                <a href="{{ route('predictions.create') }}" class="group relative overflow-hidden rounded-[24px] bg-gradient-to-r from-sky-500 to-cyan-400 p-6 shadow-lg shadow-sky-200 hover:shadow-sky-300 hover:-translate-y-1 transition-all">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 text-white backdrop-blur-sm group-hover:scale-110 transition-transform">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-white">Nueva Predicción</h3>
                            <p class="text-xs text-sky-100">Analizar calidad de agua</p>
                        </div>
                    </div>
                </a>
            </div>

            {{-- Métricas Principales --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="water-panel rounded-[24px] p-6 transition-transform hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Datasets activos</div>
                        <div class="p-2 bg-sky-50 rounded-xl text-sky-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-4xl font-semibold text-slate-900 font-['Space_Grotesk']">{{ $datasetsCount }}</div>
                    <div class="mt-2 text-xs font-medium text-slate-500">Archivos listos para análisis</div>
                </div>

                <div class="water-panel rounded-[24px] p-6 transition-transform hover:-translate-y-1">
                    <div class="flex justify-between items-start">
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Configuraciones IA</div>
                        <div class="p-2 bg-sky-50 rounded-xl text-sky-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-4xl font-semibold text-slate-900 font-['Space_Grotesk']">{{ $trainingConfigurationsCount }}</div>
                    <div class="mt-2 text-xs font-medium text-slate-500">Snapshots listos para entrenar</div>
                </div>

                <div class="water-panel rounded-[24px] p-6 relative overflow-hidden transition-transform hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-500 to-cyan-400 opacity-[0.03]"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Accuracy media</div>
                        <div class="p-2 bg-sky-500 text-white rounded-xl shadow-lg shadow-sky-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-4xl font-semibold text-sky-600 font-['Space_Grotesk'] relative z-10">{{ $averageAccuracy !== null ? number_format($averageAccuracy, 2) . '%' : 'n/d' }}</div>
                    <div class="mt-2 text-xs font-medium text-slate-500 relative z-10">{{ $completedTrainingJobsCount > 0 ? 'Promedio de jobs completados' : 'Sin jobs completados aún' }}</div>
                </div>

                <div class="water-panel rounded-[24px] p-6 transition-transform hover:-translate-y-1 border-amber-100 shadow-[0_12px_60px_rgba(245,158,11,0.05)]">
                    <div class="flex justify-between items-start">
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-amber-500">Alertas</div>
                        <div class="p-2 bg-amber-50 rounded-xl text-amber-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-4xl font-semibold text-slate-900 font-['Space_Grotesk']">{{ $alertsCount }}</div>
                    <div class="mt-2 text-xs font-medium text-slate-500">Archivos con formato erróneo</div>
                </div>
            </div>

            <div class="water-card rounded-[32px] p-8">
                <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Resumen reciente</div>
                <h3 class="mt-2 text-xl font-semibold text-slate-900 font-['Space_Grotesk']">Actividad reciente</h3>

                <div class="mt-8 grid gap-5 lg:grid-cols-[1.1fr_.9fr]">
                    <div class="space-y-4">
                        @forelse($recentActivity as $activity)
                            <div class="rounded-2xl border border-slate-100 bg-white p-4">
                                <div class="text-sm font-semibold text-slate-900">Dataset {{ $activity->original_name }}</div>
                                <div class="mt-1 text-xs font-medium text-slate-500">{{ $activity->created_at->diffForHumans() }} · {{ $activity->status }}</div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500 italic">No hay actividad reciente.</div>
                        @endforelse
                    </div>

                    <div class="rounded-2xl border border-slate-100 bg-slate-50/70 p-5">
                        <div class="text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Acceso por módulo</div>
                        <p class="mt-3 text-sm leading-6 text-slate-600">La creación y edición de datasets vive en <span class="font-semibold text-slate-900">Datasets</span>. Las configuraciones y entrenamientos viven en <span class="font-semibold text-slate-900">IA</span>.</p>
                        <div class="mt-4 space-y-2 text-sm text-slate-600">
                            <div class="rounded-xl bg-white px-4 py-3">Datasets: ver, subir y administrar.</div>
                            <div class="rounded-xl bg-white px-4 py-3">IA: configuraciones y entrenamiento.</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
