<?php

namespace App\Http\Controllers;

use App\Actions\Predictions\RunPredictionAction;
use App\Enums\TrainingJobStatus;
use App\Http\Requests\StoreDatasetPredictionRequest;
use App\Http\Requests\StorePredictionRequest;
use App\Models\Prediction;
use App\Models\TrainingJob;
use App\Repositories\Interfaces\PredictionRepositoryInterface;
use App\Services\ML\PredictionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PredictionController extends Controller
{
    public function __construct(
        private PredictionRepositoryInterface $predictionRepository
    ) {}

    public function index(Request $request)
    {
        $predictions = $this->predictionRepository->paginateForUser($request->user()->id, 10);
        return view('predictions.index', compact('predictions'));
    }

    public function create()
    {
        // Get active completed training jobs
        $activeModels = TrainingJob::query()
            ->where('status', TrainingJobStatus::COMPLETED->value)
            ->whereNotNull('model_path')
            ->with(['trainingConfiguration.dataset'])
            ->latest()
            ->get();
            
        return view('predictions.create', compact('activeModels'));
    }

    public function dataset(Request $request)
    {
        $activeModels = TrainingJob::query()
            ->where('status', TrainingJobStatus::COMPLETED->value)
            ->whereNotNull('model_path')
            ->with(['trainingConfiguration.dataset'])
            ->latest()
            ->get();

        return view('predictions.dataset', [
            'activeModels' => $activeModels,
            'batchResult' => $request->session()->get('batch_result'),
            'batchModelId' => $request->session()->get('batch_model_id'),
            'batchDatasetName' => $request->session()->get('batch_dataset_name'),
            'batchAlgorithm' => $request->session()->get('batch_algorithm'),
        ]);
    }

    public function store(StorePredictionRequest $request, RunPredictionAction $action)
    {
        Log::info('Prediction store hit', [
            'user_id' => $request->user()?->id,
            'training_job_id' => $request->training_job_id,
        ]);

        $job = TrainingJob::findOrFail($request->training_job_id);
        
        try {
            $prediction = $action->execute(
                $job, 
                $request->getInputData(), 
                $request->user()->id
            );
            
            return redirect()->route('predictions.show', $prediction)
                ->with('success', 'Predicción ejecutada exitosamente.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function storeDataset(StoreDatasetPredictionRequest $request, PredictionService $predictionService)
    {
        $job = TrainingJob::findOrFail($request->training_job_id);

        try {
            $batchResult = $predictionService->predictDataset($job);

            // Build summary values for storage and display
            $batchPotable = (int) ($batchResult['summary']['potable'] ?? 0);
            $batchNonPotable = (int) ($batchResult['summary']['non_potable'] ?? 0);
            $batchTotal = max((int) ($batchResult['total'] ?? 0), 1);
            $batchIsPotable = $batchPotable >= $batchNonPotable;
            $batchConfidence = round((max($batchPotable, $batchNonPotable) / $batchTotal) * 100, 1);

            // Create a single summary Prediction record for this dataset run if none exists yet
            $existing = \App\Models\Prediction::where('training_job_id', $job->id)
                ->whereJsonContains('input_data->batch', true)
                ->first();

            if (! $existing) {
                $dto = new \App\DTOs\PredictionDTO(
                    $request->user()->id,
                    $job->id,
                    [
                        'batch' => true,
                        'summary' => $batchResult['summary'] ?? [],
                        'total' => $batchResult['total'] ?? 0,
                    ],
                    $batchIsPotable ? 'Potable' : 'No Potable',
                    $batchConfidence / 100,
                    isset($batchResult['execution_time_ms']) ? (int) $batchResult['execution_time_ms'] : null
                );

                $this->predictionRepository->create($dto);
            }

            return redirect()
                ->route('predictions.dataset')
                ->with([
                    'batch_result' => $batchResult,
                    'batch_model_id' => $job->id,
                    'batch_dataset_name' => $job->trainingConfiguration?->dataset?->name ?? 'Dataset asociado',
                    'batch_algorithm' => $job->algorithm->value,
                    'success' => 'Predicción del dataset ejecutada exitosamente.',
                ]);
        } catch (\Throwable $throwable) {
            return redirect()
                ->route('predictions.dataset')
                ->with('error', $throwable->getMessage());
        }
    }

    public function show(Prediction $prediction)
    {
        Gate::authorize('view', $prediction);
        return view('predictions.show', compact('prediction'));
    }
}
