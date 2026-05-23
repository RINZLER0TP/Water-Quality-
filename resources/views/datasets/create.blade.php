<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold text-gray-800 leading-tight">Subir dataset</h2>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-3xl sm:px-6 lg:px-8">
            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-200">
                <form method="POST" action="{{ route('datasets.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nombre</label>
                        <input id="name" name="name" value="{{ old('name') }}" placeholder="Ej: Calidad agua pozo 01"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="dataset_file" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
                        <input id="dataset_file" name="dataset_file" type="file" accept=".csv,.txt"
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-slate-500 focus:ring-slate-500">
                        <p class="mt-1 text-xs text-gray-500">Se almacena en storage/app/weka/datasets.</p>
                        @error('dataset_file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('datasets.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Cancelar</a>
                        <button class="rounded-md bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800">Guardar dataset</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
