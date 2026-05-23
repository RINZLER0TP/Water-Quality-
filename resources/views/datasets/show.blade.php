<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 leading-tight">Detalle del dataset</h2>
                <p class="mt-1 text-sm text-gray-500">Información completa y metadata del archivo cargado.</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('datasets.download', $dataset) }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Descargar</a>
                <form method="POST" action="{{ route('datasets.destroy', $dataset) }}" onsubmit="return confirm('¿Eliminar dataset?')">
                    @csrf
                    @method('DELETE')
                    <button class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Eliminar</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-5xl space-y-6 sm:px-6 lg:px-8">
            <div class="grid gap-6 md:grid-cols-2">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Datos generales</h3>
                    <dl class="mt-4 space-y-4 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Nombre</dt><dd class="font-medium text-gray-900">{{ $dataset->name }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Original</dt><dd class="font-medium text-gray-900">{{ $dataset->original_name }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Estado</dt><dd class="font-medium text-gray-900">{{ $dataset->status->value }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Filas</dt><dd class="font-medium text-gray-900">{{ $dataset->rows_count }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Columnas</dt><dd class="font-medium text-gray-900">{{ $dataset->columns_count }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Tamaño</dt><dd class="font-medium text-gray-900">{{ number_format($dataset->file_size / 1024, 2) }} KB</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-gray-500">Uploader</dt><dd class="font-medium text-gray-900">{{ $dataset->uploader?->name ?? 'N/A' }}</dd></div>
                    </dl>
                </div>

                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Metadata</h3>
                    <pre class="mt-4 overflow-x-auto rounded-xl bg-gray-950 p-4 text-sm text-gray-100">{{ json_encode($dataset->metadata ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Ruta almacenada</h3>
                <p class="mt-2 text-sm text-gray-600">{{ $dataset->file_path }}</p>
            </div>
        </div>
    </div>
</x-app-layout>
