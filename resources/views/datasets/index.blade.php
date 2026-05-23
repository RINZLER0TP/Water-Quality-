<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 leading-tight">Datasets</h2>
                <p class="mt-1 text-sm text-gray-500">Administra los archivos CSV usados para entrenamiento y validación.</p>
            </div>
            <a href="{{ route('datasets.create') }}" class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">
                Subir dataset
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form method="GET" action="{{ route('datasets.index') }}" class="grid gap-4 md:grid-cols-3">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700">Buscar</label>
                        <input id="search" name="search" value="{{ request('search') }}" placeholder="Nombre, archivo, estado o uploader"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    </div>
                    <div>
                        <label for="per_page" class="block text-sm font-medium text-gray-700">Por página</label>
                        <select id="per_page" name="per_page" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                            @foreach ([10, 15, 25, 50] as $value)
                                <option value="{{ $value }}" @selected((int) request('per_page', 10) === $value)>{{ $value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3 flex justify-end">
                        <button class="inline-flex items-center rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Filtrar</button>
                    </div>
                </form>
            </div>

            <div class="overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Filas / Columnas</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Uploader</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($datasets as $dataset)
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ $dataset->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $dataset->original_name }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $dataset->status->value }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $dataset->rows_count }} / {{ $dataset->columns_count }}</td>
                                <td class="px-6 py-4 text-sm text-gray-700">{{ $dataset->uploader?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('datasets.show', $dataset) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50">Ver</a>
                                        <a href="{{ route('datasets.download', $dataset) }}" class="rounded-md border border-gray-300 px-3 py-1.5 text-gray-700 hover:bg-gray-50">Descargar</a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-500">No hay datasets registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $datasets->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
