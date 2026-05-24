<?php

namespace App\Http\Controllers;

use App\Actions\Predictions\RunPredictionAction;
use App\Http\Requests\StorePredictionRequest;
use App\Models\Prediction;
use App\Models\TrainingJob;
use App\Repositories\Interfaces\PredictionRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

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
        $activeModels = TrainingJob::where('status', 'COMPLETED')
            ->whereNotNull('model_path')
            ->latest()
            ->get();
            
        return view('predictions.create', compact('activeModels'));
    }

    public function store(StorePredictionRequest $request, RunPredictionAction $action)
    {
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

    public function show(Prediction $prediction)
    {
        Gate::authorize('view', $prediction);
        return view('predictions.show', compact('prediction'));
    }
}
