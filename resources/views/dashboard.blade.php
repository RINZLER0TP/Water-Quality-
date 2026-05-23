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
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Modelos IA</div>
                        <div class="p-2 bg-sky-50 rounded-xl text-sky-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-4xl font-semibold text-slate-900 font-['Space_Grotesk']">{{ $modelsCount }}</div>
                    <div class="mt-2 text-xs font-medium text-slate-500">Entrenados con Weka</div>
                </div>

                <div class="water-panel rounded-[24px] p-6 relative overflow-hidden transition-transform hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-500 to-cyan-400 opacity-[0.03]"></div>
                    <div class="flex justify-between items-start relative z-10">
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Precisión global</div>
                        <div class="p-2 bg-sky-500 text-white rounded-xl shadow-lg shadow-sky-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                    <div class="mt-4 text-4xl font-semibold text-sky-600 font-['Space_Grotesk'] relative z-10">{{ $accuracy }}%</div>
                    <div class="mt-2 text-xs font-medium text-slate-500 relative z-10">Esperando datos Weka</div>
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

            {{-- Paneles Inferiores --}}
            <div class="grid gap-6 lg:grid-cols-[1.2fr_.8fr]">
                {{-- Acciones y Flujos --}}
                <div class="water-panel rounded-[32px] p-8 flex flex-col justify-between">
                    <div>
                        <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Gestión Operativa</div>
                        <h3 class="mt-2 text-2xl font-semibold text-slate-900 font-['Space_Grotesk']">Flujos principales</h3>
                        <p class="mt-2 text-sm text-slate-500 max-w-md">Ejecuta las tareas más comunes de tu pipeline de aprendizaje automático para análisis de calidad de agua.</p>
                    </div>

                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        <x-water-button href="{{ route('datasets.index') }}" class="w-full justify-center">Ver todos los datasets</x-water-button>
                        <x-water-button href="{{ route('datasets.create') }}" class="w-full justify-center">Cargar nuevo CSV</x-water-button>
                        
                        <div class="sm:col-span-2 mt-2 p-6 rounded-2xl border border-sky-100 bg-sky-50/50 flex items-center justify-between group cursor-pointer transition-colors hover:bg-sky-50">
                            <div>
                                <div class="font-semibold text-slate-900 group-hover:text-sky-700 transition-colors">Entrenar nuevo modelo IA</div>
                                <div class="text-xs text-slate-500 mt-1">Requiere al menos un dataset válido con variable objetivo.</div>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-sky-500 shadow-sm transition-transform group-hover:scale-110 group-hover:text-sky-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actividad --}}
                <div class="water-card rounded-[32px] p-8">
                    <div class="text-[11px] font-bold uppercase tracking-[0.2em] text-sky-500">Bitácora</div>
                    <h3 class="mt-2 text-xl font-semibold text-slate-900 font-['Space_Grotesk']">Actividad reciente</h3>

                    <div class="mt-8 space-y-6 relative before:absolute before:inset-0 before:ml-2 md:before:mx-0 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-200 before:to-transparent">
                        
                        @forelse($recentActivity as $activity)
                            <div class="relative flex items-start gap-4">
                                <div class="z-10 flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-cyan-400 ring-4 ring-white mt-1"></div>
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">Dataset `{{ $activity->original_name }}` cargado</div>
                                    <div class="text-xs font-medium text-slate-500 mt-0.5">{{ $activity->created_at->diffForHumans() }} · {{ $activity->status }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-slate-500 italic">No hay actividad reciente. Empieza subiendo un dataset.</div>
                        @endforelse

                    </div>

                    <div class="mt-8 rounded-2xl bg-white p-4 border border-slate-100 shadow-sm">
                        <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400">Servicios operativos</div>
                        <div class="mt-3 flex flex-wrap gap-2 text-[11px] font-semibold text-slate-600">
                            <span class="flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1 ring-1 ring-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> BD Activa
                            </span>
                            <span class="flex items-center gap-1.5 rounded-full bg-slate-50 px-3 py-1 ring-1 ring-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> Weka Engine
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
