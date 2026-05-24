<?php

namespace App\Http\Controllers;

use App\Actions\StoreTrainingConfigurationAction;
use App\Enums\TrainingAlgorithm;
use App\Http\Requests\IndexTrainingConfigurationRequest;
use App\Http\Requests\PreviewTrainingDatasetRequest;
use App\Http\Requests\StoreTrainingConfigurationRequest;
use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Services\TrainingConfigurations\TrainingConfigurationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrainingConfigurationController extends Controller
{
    public function __construct(private TrainingConfigurationService $service)
    {
    }

    public function index(IndexTrainingConfigurationRequest $request): View
    {
        $this->authorize('viewAny', TrainingConfiguration::class);

        $search = $request->validated('search', '');
        $perPage = (int) $request->validated('per_page', 12);

        $configurations = $this->service->paginate($search, $perPage);
        $statistics = $this->service->summary($search);

        return view('training-configurations.index', compact('configurations', 'statistics', 'search', 'perPage'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TrainingConfiguration::class);

        $datasets = Dataset::query()->withoutTrashed()->with('uploader')->orderByDesc('created_at')->get();
        $selectedDatasetId = (int) $request->integer('dataset_id');
        $selectedDataset = $selectedDatasetId > 0 ? Dataset::query()->withoutTrashed()->with('uploader')->find($selectedDatasetId) : null;
        $preview = null;

        if ($selectedDataset) {
            $this->authorize('view', $selectedDataset);
            $preview = $this->service->previewDataset($selectedDataset);
        }

        $algorithms = TrainingAlgorithm::options();
        $algorithmSchemas = collect(TrainingAlgorithm::cases())->mapWithKeys(static fn (TrainingAlgorithm $algorithm): array => [
            $algorithm->value => $algorithm->parameterSchema(),
        ])->all();
        $algorithmDefaults = collect(TrainingAlgorithm::cases())->mapWithKeys(static fn (TrainingAlgorithm $algorithm): array => [
            $algorithm->value => $algorithm->defaultParameters(),
        ])->all();

        return view('training-configurations.create', compact('datasets', 'selectedDataset', 'preview', 'algorithms', 'algorithmSchemas', 'algorithmDefaults'));
    }

    public function store(StoreTrainingConfigurationRequest $request, StoreTrainingConfigurationAction $action): RedirectResponse
    {
        $dataset = Dataset::query()->withoutTrashed()->with('uploader')->findOrFail((int) $request->validated('dataset_id'));

        $this->authorize('create', [TrainingConfiguration::class, $dataset]);

        $configuration = $action(
            $request->user(),
            $dataset,
            $request->validated('target_column'),
            TrainingAlgorithm::from($request->validated('algorithm')),
            $request->validated('parameters', [])
        );

        return redirect()
            ->route('training-configurations.show', $configuration)
            ->with('status', 'Configuración de entrenamiento creada correctamente.');
    }

    public function show(TrainingConfiguration $trainingConfiguration): View
    {
        $this->authorize('view', $trainingConfiguration);

        $trainingConfiguration->loadMissing(['dataset.uploader', 'creator']);

        return view('training-configurations.show', compact('trainingConfiguration'));
    }

    public function preview(Dataset $dataset, PreviewTrainingDatasetRequest $request): JsonResponse
    {
        $this->authorize('view', $dataset);

        $preview = $this->service->previewDataset($dataset, (int) $request->validated('limit', 10));

        return response()->json($preview);
    }

    public function destroy(TrainingConfiguration $trainingConfiguration): RedirectResponse
    {
        $this->authorize('delete', $trainingConfiguration);

        $this->service->delete($trainingConfiguration);

        return redirect()->route('training-configurations.index')->with('status', 'Configuración de entrenamiento eliminada correctamente.');
    }
}