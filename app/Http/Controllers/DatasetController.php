<?php

namespace App\Http\Controllers;

use App\Actions\DeleteDatasetAction;
use App\Actions\StoreDatasetAction;
use App\Http\Requests\IndexDatasetRequest;
use App\Http\Requests\StoreDatasetRequest;
use App\Models\Dataset;
use App\Services\Datasets\DatasetService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatasetController extends Controller
{
    public function __construct(private DatasetService $service)
    {
    }

    public function index(IndexDatasetRequest $request): View
    {
        $this->authorize('viewAny', Dataset::class);

        $search = $request->validated('search', '');
        $perPage = (int) $request->validated('per_page', 12);

        $datasets = $this->service->paginate($search, $perPage);
        $statistics = $this->service->summary($search);

        return view('datasets.index', compact('datasets', 'statistics', 'search', 'perPage'));
    }

    public function create(): View
    {
        $this->authorize('create', Dataset::class);

        return view('datasets.create');
    }

    public function store(StoreDatasetRequest $request, StoreDatasetAction $action): RedirectResponse
    {
        try {
            $dataset = $action(
                $request->user(),
                $request->validated('name'),
                $request->file('dataset_file')
            );
        } catch (RuntimeException $exception) {
            return back()
                ->withInput()
                ->withErrors(['dataset_file' => $exception->getMessage()]);
        }

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
        $this->authorize('download', $dataset);

        return $this->service->download($dataset);
    }

    public function destroy(Request $request, Dataset $dataset, DeleteDatasetAction $action): RedirectResponse
    {
        $this->authorize('delete', $dataset);

        $user = $request->user();

        if ($user === null) {
            abort(403);
        }

        $action($user, $dataset);

        return redirect()->route('datasets.index')->with('status', 'Dataset eliminado correctamente.');
    }
}
