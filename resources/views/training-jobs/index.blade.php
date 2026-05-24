<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-2xl space-y-2">
                <x-water-chip value="IA / Weka" label="Entrenamientos ejecutados" />
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Historial de entrenamientos</h2>
                <p class="text-sm text-slate-500">Ejecuta configuraciones guardadas, revisa métricas, monitorea estados y descarga modelos .model.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <x-water-button href="{{ route('training-jobs.create') }}" class="bg-slate-950 text-white shadow-lg shadow-cyan-900/15">Nuevo entrenamiento</x-water-button>
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

            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Entrenamientos</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['total_jobs']) }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Completados</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['completed']) }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">En ejecución</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['running']) }}</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Accuracy promedio</p>
                    <p class="mt-2 text-3xl font-semibold text-slate-950">{{ number_format($statistics['average_accuracy'] * 100, 2) }}%</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Último completado</p>
                    <p class="mt-2 text-lg font-semibold text-slate-950">{{ $statistics['latest_completed_at'] ? \Illuminate\Support\Carbon::parse($statistics['latest_completed_at'])->format('d/m/Y H:i') : 'n/a' }}</p>
                </div>
            </section>

            <section class="rounded-[28px] border border-slate-200 bg-white p-5 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                <form method="GET" action="{{ route('training-jobs.index') }}" class="grid gap-4 lg:grid-cols-[1fr_180px_auto] lg:items-end">
                    <div>
                        <label for="search" class="block text-sm font-medium text-slate-700">Buscar entrenamientos</label>
                        <input id="search" name="search" value="{{ $search }}" placeholder="Algoritmo, estado, target o dataset" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100">
                    </div>
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-slate-700">Por página</label>
                        <select id="per_page" name="per_page" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100">
                            @foreach ([12, 24, 36, 48] as $value)
                                <option value="{{ $value }}" @selected((int) $perPage === $value)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex gap-3 lg:justify-end">
                        <x-water-button type="submit" class="bg-slate-950 text-white shadow-lg shadow-cyan-900/10">Filtrar</x-water-button>
                        <x-water-button href="{{ route('training-jobs.index') }}" class="border border-slate-200 bg-white text-slate-700">Limpiar</x-water-button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-[32px] border border-slate-200 bg-white shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/80">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Configuración</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Estado</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Métricas</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Tiempo</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Creador</th>
                                <th class="px-6 py-4 text-right text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @foreach ($jobs as $job)
                                <tr class="transition hover:bg-slate-50/60">
                                    <td class="px-6 py-5 align-top">
                                        <div class="space-y-1">
                                            <div class="text-sm font-semibold text-slate-950">{{ $job->trainingConfiguration?->dataset?->name ?? 'Configuración' }}</div>
                                            <div class="text-sm text-slate-500">{{ $job->algorithm->label() }}</div>
                                            <div class="text-xs text-slate-400">Target: {{ $job->target_column }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold ring-1 bg-cyan-50 text-cyan-700 ring-cyan-200">{{ $job->status->label() }}</span>
                                    </td>
                                    <td class="px-6 py-5 align-top text-sm text-slate-600">
                                        <div class="space-y-1">
                                            <div>Accuracy: {{ isset($job->metrics['accuracy']) ? number_format((float) $job->metrics['accuracy'] * 100, 2) . '%' : 'n/a' }}</div>
                                            <div>F1: {{ isset($job->metrics['f1_score']) ? number_format((float) $job->metrics['f1_score'] * 100, 2) . '%' : 'n/a' }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top text-sm text-slate-600">{{ $job->training_time_ms ? number_format($job->training_time_ms / 1000, 2).' s' : 'n/a' }}</td>
                                    <td class="px-6 py-5 align-top text-sm text-slate-600">
                                        <div class="space-y-1">
                                            <div class="font-medium text-slate-900">{{ $job->creator?->name ?? 'Sin creador' }}</div>
                                            <div class="text-xs text-slate-400">{{ $job->created_at?->format('d/m/Y H:i') }}</div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 align-top">
                                        <div class="flex justify-end gap-2">
                                            <x-water-button href="{{ route('training-jobs.show', $job) }}" class="border border-slate-200 bg-white text-slate-700">Ver</x-water-button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>

            <div>
                {{ $jobs->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
