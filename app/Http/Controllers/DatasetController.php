<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexDatasetRequest;
use App\Http\Requests\StoreDatasetRequest;
use App\Models\Dataset;
use App\Services\Datasets\DatasetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatasetController extends Controller
{
    public function __construct(private DatasetService $service)
    {
    }

    public function index(IndexDatasetRequest $request): View
    {
        $this->authorize('viewAny', Dataset::class);

        $datasets = $this->service->paginate(
            $request->validated('search', ''),
            (int) $request->validated('per_page', 10)
        );

        return view('datasets.index', compact('datasets'));
    }

    public function create(): View
    {
        $this->authorize('create', Dataset::class);

        return view('datasets.create');
    }

    public function store(StoreDatasetRequest $request): RedirectResponse
    {
        $dataset = $this->service->store(
            $request->user(),
            $request->validated('name'),
            $request->file('dataset_file')
        );

        return redirect()->route('datasets.show', $dataset)->with('status', 'Dataset cargado y validado correctamente.');
    }

    public function show(Dataset $dataset): View
    {
        $this->authorize('view', $dataset);

        $dataset->loadMissing('uploader');

        return view('datasets.show', compact('dataset'));
    }

    public function download(Dataset $dataset): BinaryFileResponse
    {
        $this->authorize('view', $dataset);

        return $this->service->download($dataset);
    }

    public function destroy(Dataset $dataset): RedirectResponse
    {
        $this->authorize('delete', $dataset);

        $this->service->delete($dataset);

        return redirect()->route('datasets.index')->with('status', 'Dataset eliminado correctamente.');
    }
}
