<?php

namespace App\Http\Controllers;

use App\Actions\RunTrainingJobAction;
use App\Http\Requests\IndexTrainingJobRequest;
use App\Http\Requests\StoreTrainingJobRequest;
use App\Models\TrainingConfiguration;
use App\Models\TrainingJob;
use App\Services\TrainingJobs\TrainingJobService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TrainingJobController extends Controller
{
    public function __construct(private TrainingJobService $service)
    {
    }

    public function index(IndexTrainingJobRequest $request): View
    {
        $this->authorize('viewAny', TrainingJob::class);

        $search = $request->validated('search', '');
        $perPage = (int) $request->validated('per_page', 12);

        $jobs = $this->service->paginate($search, $perPage);
        $statistics = $this->service->summary($search);

        return view('training-jobs.index', compact('jobs', 'statistics', 'search', 'perPage'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', TrainingJob::class);

        $configurations = TrainingConfiguration::query()
            ->withoutTrashed()
            ->with(['dataset.uploader', 'creator'])
            ->orderByDesc('created_at')
            ->get();

        $selectedConfiguration = null;

        $configurationId = (int) $request->integer('training_configuration_id');
        if ($configurationId > 0) {
            $selectedConfiguration = TrainingConfiguration::query()
                ->withoutTrashed()
                ->with(['dataset.uploader', 'creator'])
                ->find($configurationId);
        }

        return view('training-jobs.create', compact('configurations', 'selectedConfiguration'));
    }

    public function store(StoreTrainingJobRequest $request, RunTrainingJobAction $action): RedirectResponse
    {
        $configuration = TrainingConfiguration::query()
            ->withoutTrashed()
            ->with(['dataset.uploader', 'creator'])
            ->findOrFail((int) $request->validated('training_configuration_id'));

        $this->authorize('create', [TrainingJob::class, $configuration]);

        $job = $action($request->user(), $configuration);

        $message = $job->status->value === 'failed'
            ? 'El entrenamiento falló, pero el job quedó guardado con el error para su revisión.'
            : 'Entrenamiento ejecutado y guardado correctamente.';

        return redirect()
            ->route('training-jobs.show', $job)
            ->with($job->status->value === 'failed' ? 'warning' : 'status', $message);
    }

    public function show(TrainingJob $trainingJob): View
    {
        $this->authorize('view', $trainingJob);

        $trainingJob->loadMissing(['trainingConfiguration.dataset.uploader', 'creator']);

        return view('training-jobs.show', compact('trainingJob'));
    }

    public function metrics(TrainingJob $trainingJob): JsonResponse
    {
        $this->authorize('view', $trainingJob);

        return response()->json($this->service->metrics($trainingJob));
    }

    public function destroy(TrainingJob $trainingJob): RedirectResponse
    {
        $this->authorize('delete', $trainingJob);

        $trainingJob->delete();

        return redirect()->route('training-jobs.index')->with('status', 'Entrenamiento eliminado correctamente.');
    }
}
