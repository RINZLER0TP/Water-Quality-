<x-app-layout>
    <x-slot name="header">
        <div class="max-w-2xl space-y-2">
            <x-water-chip value="IA / Weka" label="Nuevo entrenamiento" />
            <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Ejecutar entrenamiento automático</h2>
            <p class="text-sm text-slate-500">Selecciona una configuración guardada y Laravel lanzará el engine Java con Weka para generar métricas y modelo.</p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 px-4 sm:px-6 lg:px-8">
            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Pipeline</p>
                    <p class="mt-2 text-xl font-semibold text-slate-950">Laravel + Java + Weka</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Salida</p>
                    <p class="mt-2 text-xl font-semibold text-slate-950">.model + métricas</p>
                </div>
                <div class="rounded-[24px] border border-slate-200 bg-white p-5 shadow-[0_10px_40px_rgba(15,23,42,0.06)]">
                    <p class="text-sm text-slate-500">Estados</p>
                    <p class="mt-2 text-xl font-semibold text-slate-950">pending / running / completed / failed</p>
                </div>
            </section>

            @if ($selectedConfiguration)
                <section class="rounded-[28px] border border-cyan-200 bg-cyan-50/50 p-5">
                    <p class="text-sm font-medium text-cyan-800">Configuración seleccionada</p>
                    <div class="mt-2 grid gap-3 md:grid-cols-3">
                        <div>
                            <p class="text-xs uppercase tracking-wide text-cyan-700">Dataset</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $selectedConfiguration->dataset?->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-cyan-700">Algoritmo</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $selectedConfiguration->algorithm->label() }}</p>
                        </div>
                        <div>
                            <p class="text-xs uppercase tracking-wide text-cyan-700">Target</p>
                            <p class="mt-1 font-semibold text-slate-950">{{ $selectedConfiguration->target_column }}</p>
                        </div>
                    </div>
                </section>
            @endif

            <section class="rounded-[32px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                <form method="GET" action="{{ route('training-jobs.create') }}" class="space-y-6">
                    <div>
                        <label for="training_configuration_id" class="block text-sm font-medium text-slate-700">Configuración de entrenamiento</label>
                        <select id="training_configuration_id" name="training_configuration_id" class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100">
                            <option value="">Selecciona una configuración</option>
                            @foreach ($configurations as $configuration)
                                <option value="{{ $configuration->id }}" @selected($selectedConfiguration?->id === $configuration->id)>
                                    {{ $configuration->dataset?->name }} · {{ $configuration->algorithm->label() }} · {{ $configuration->target_column }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="rounded-[28px] border border-dashed border-slate-200 bg-slate-50/70 p-5">
                        <p class="text-sm font-semibold text-slate-900">Qué ocurre al ejecutar</p>
                        <div class="mt-3 grid gap-3 md:grid-cols-2 text-sm text-slate-600">
                            <div class="rounded-2xl bg-white p-4">Laravel guarda el job como <span class="font-semibold text-slate-950">running</span>.</div>
                            <div class="rounded-2xl bg-white p-4">Java carga CSV, entrena y valida con Weka.</div>
                            <div class="rounded-2xl bg-white p-4">Se exporta el modelo a storage/app/weka/models.</div>
                            <div class="rounded-2xl bg-white p-4">Se persisten accuracy, precision, recall, F1 y matriz de confusión.</div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <x-water-button type="submit" class="bg-slate-950 text-white">Entrenar ahora</x-water-button>
                        <x-water-button href="{{ route('training-jobs.index') }}" class="border border-slate-200 bg-white text-slate-700">Volver</x-water-button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</x-app-layout>
