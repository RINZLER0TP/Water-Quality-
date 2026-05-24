<?php

namespace Tests\Feature;

use App\Actions\RunTrainingJobAction;
use App\Enums\DatasetStatus;
use App\Enums\TrainingJobStatus;
use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Models\TrainingJob;
use App\Models\User;
use App\Services\ML\JavaWekaClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class TrainingJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_execute_a_training_job_and_store_metrics(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $dataset = $this->createDataset($user);
        $configuration = $this->createConfiguration($user, $dataset);

        Storage::disk('local')->makeDirectory('weka/models');
        $modelPath = Storage::disk('local')->path('weka/models/training-job-1.model');
        file_put_contents($modelPath, 'model-bytes');

        $this->app->instance(JavaWekaClient::class, Mockery::mock(JavaWekaClient::class, function ($mock) use ($modelPath): void {
            $mock->shouldReceive('runJar')
                ->once()
                ->andReturn(json_encode([
                    'success' => true,
                    'model_path' => 'weka/models/training-job-1.model',
                    'metrics' => [
                        'accuracy' => 0.91,
                        'precision' => 0.89,
                        'recall' => 0.88,
                        'f1_score' => 0.885,
                        'training_time_ms' => 1234,
                    ],
                    'confusion_matrix' => [[8, 2], [1, 9]],
                ]));
        }));

        $response = $this->actingAs($user)->post(route('training-jobs.store'), [
            'training_configuration_id' => $configuration->id,
        ]);

        $job = TrainingJob::query()->firstOrFail();

        $response->assertRedirect(route('training-jobs.show', $job));
        $this->assertSame(TrainingJobStatus::COMPLETED->value, $job->status->value);
        $this->assertSame(0.91, (float) $job->metrics['accuracy']);
        $this->assertSame(0.885, (float) $job->metrics['f1_score']);
        $this->assertSame([[8, 2], [1, 9]], $job->confusion_matrix);
        $this->assertNotNull($job->completed_at);
    }

    public function test_user_can_view_training_job_metrics_as_json(): void
    {
        $user = User::factory()->create();
        $dataset = $this->createDataset($user);
        $configuration = $this->createConfiguration($user, $dataset);

        $job = TrainingJob::create([
            'training_configuration_id' => $configuration->id,
            'created_by' => $user->id,
            'dataset_id' => $dataset->id,
            'algorithm' => 'logistic',
            'target_column' => 'status',
            'parameters' => [],
            'status' => TrainingJobStatus::COMPLETED->value,
            'metrics' => [
                'accuracy' => 0.95,
                'precision' => 0.93,
                'recall' => 0.92,
                'f1_score' => 0.925,
                'training_time_ms' => 900,
            ],
            'confusion_matrix' => [[9, 1], [0, 10]],
            'cross_validation_folds' => 10,
            'random_seed' => 42,
        ]);

        $response = $this->actingAs($user)->get(route('training-jobs.metrics', $job));

        $response->assertOk();
        $response->assertJsonPath('metrics.accuracy', 0.95);
        $response->assertJsonPath('metrics.f1_score', 0.925);
        $response->assertJsonPath('confusion_matrix.0.0', 9);
    }

    private function createDataset(User $user): Dataset
    {
        $filePath = 'weka/datasets/training.csv';

        Storage::disk('local')->put($filePath, "ph,temperature,status\n7.2,23.1,normal\n8.0,22.4,alert\n7.5,24.0,normal\n");

        return Dataset::create([
            'name' => 'Dataset training job',
            'original_name' => 'training.csv',
            'file_path' => $filePath,
            'file_size' => strlen("ph,temperature,status\n7.2,23.1,normal\n8.0,22.4,alert\n7.5,24.0,normal\n"),
            'rows_count' => 3,
            'columns_count' => 3,
            'status' => DatasetStatus::VALIDATED->value,
            'uploaded_by' => $user->id,
            'metadata' => [],
            'metrics' => [],
        ]);
    }

    private function createConfiguration(User $user, Dataset $dataset): TrainingConfiguration
    {
        return TrainingConfiguration::create([
            'dataset_id' => $dataset->id,
            'created_by' => $user->id,
            'target_column' => 'status',
            'algorithm' => 'logistic',
            'parameters' => [],
            'analysis' => [
                'statistics' => ['rows_count' => 3],
                'columns' => [
                    ['name' => 'ph', 'type' => 'numeric'],
                    ['name' => 'temperature', 'type' => 'numeric'],
                    ['name' => 'status', 'type' => 'categorical'],
                ],
                'suggested_target' => ['name' => 'status'],
                'compatibility' => ['is_compatible' => true, 'issues' => []],
            ],
        ]);
    }
}
