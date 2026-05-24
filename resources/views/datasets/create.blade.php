<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-2xl font-semibold tracking-tight text-slate-900 leading-tight">Subir dataset CSV</h2>
                <p class="mt-1 text-sm text-slate-500">Validación estricta, metadata automática y almacenamiento seguro para el pipeline de machine learning.</p>
            </div>
            <x-water-button href="{{ route('datasets.index') }}" class="border border-slate-200 bg-white text-slate-700">Volver</x-water-button>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <x-water-shell>
                <x-slot name="aside">
                    <div class="space-y-6">
                        <div class="space-y-3">
                            <x-water-chip value="CSV strict" label="Validación estructural" />
                            <h3 class="text-3xl font-semibold tracking-tight text-white">Dataset listo para Weka, sin pasos intermedios.</h3>
                            <p class="text-sm leading-6 text-cyan-50/90">El archivo se inspecciona antes de persistirse. Si faltan encabezados, hay filas inconsistentes o el formato no es CSV real, la carga se rechaza.</p>
                        </div>

                        <div class="grid gap-3">
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-cyan-50/70">Almacenamiento</p>
                                <p class="mt-2 text-sm text-white">storage/app/weka/datasets</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-cyan-50/70">Extracción</p>
                                <p class="mt-2 text-sm text-white">Filas, columnas, tamaño y encabezados</p>
                            </div>
                            <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                                <p class="text-xs uppercase tracking-[0.2em] text-cyan-50/70">Salida</p>
                                <p class="mt-2 text-sm text-white">Dataset validado para entrenamiento automático</p>
                            </div>
                        </div>
                    </div>
                </x-slot>

                <div class="space-y-6">
                    @if (session('error'))
                        <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-800 shadow-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="rounded-[28px] border border-slate-200 bg-white p-6 shadow-[0_20px_80px_rgba(15,23,42,0.08)]">
                        <form method="POST" action="{{ route('datasets.store') }}" enctype="multipart/form-data" class="space-y-6">
                            @csrf

                            <div class="grid gap-5 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-slate-700">Nombre del dataset</label>
                                    <input id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Agua potable zona norte"
                                        class="mt-2 w-full rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-cyan-400 focus:bg-white focus:ring-4 focus:ring-cyan-100">
                                    @error('name')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>

                                <div class="md:col-span-2">
                                    <label for="dataset_file" class="block text-sm font-medium text-slate-700">Archivo CSV</label>
                                    <input id="dataset_file" name="dataset_file" type="file" accept=".csv"
                                        class="mt-2 w-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-4 text-sm text-slate-700 shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-slate-950 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:border-cyan-300 hover:bg-white">
                                    <div class="mt-2 flex flex-wrap gap-2 text-xs text-slate-500">
                                        <x-water-chip value=".csv" label="Extensión obligatoria" />
                                        <x-water-chip value="50 MB" label="Tamaño máximo" />
                                        <x-water-chip value="validación" label="Encabezados y filas" />
                                    </div>
                                    @error('dataset_file')<p class="mt-2 text-sm text-rose-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 pt-5">
                                <x-water-button href="{{ route('datasets.index') }}" class="border border-slate-200 bg-white text-slate-700">Cancelar</x-water-button>
                                <x-water-button type="submit" class="bg-slate-950 text-white shadow-lg shadow-cyan-900/15">Guardar y validar</x-water-button>
                            </div>
                        </form>
                    </div>
                </div>
            </x-water-shell>
        </div>
    </div>
</x-app-layout>
