<?php

namespace Tests\Feature;

use App\Enums\DatasetStatus;
use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Models\TrainingJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DatasetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_a_valid_csv_dataset(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('datasets.store'), [
            'name' => 'Dataset laboratorio',
            'dataset_file' => UploadedFile::fake()->createWithContent('dataset.csv', "ph,temperature,status\n7.2,23.1,normal\n8.0,22.4,alert\n"),
        ]);

        $dataset = Dataset::query()->first();

        $response->assertRedirect(route('datasets.show', $dataset));
        $response->assertSessionHas('status', 'Dataset cargado y validado correctamente.');

        $this->assertNotNull($dataset);
        $this->assertSame('Dataset laboratorio', $dataset->name);
        $this->assertSame($user->id, $dataset->uploaded_by);
        $this->assertSame(2, $dataset->rows_count);
        $this->assertSame(3, $dataset->columns_count);
        $this->assertSame('validated', $dataset->status->value);
        $this->assertTrue(Storage::disk('local')->exists($dataset->file_path));
    }

    public function test_invalid_csv_is_rejected_and_not_stored(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('datasets.create'))->post(route('datasets.store'), [
            'name' => 'CSV roto',
            'dataset_file' => UploadedFile::fake()->createWithContent('broken.csv', "ph,temperature\n7.2\n"),
        ]);

        $response->assertRedirect(route('datasets.create'));
        $response->assertSessionHasErrors('dataset_file');
        $this->assertDatabaseCount('datasets', 0);
    }

    public function test_dataset_can_be_deleted_and_file_removed(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('datasets.store'), [
            'name' => 'Dataset a borrar',
            'dataset_file' => UploadedFile::fake()->createWithContent('delete.csv', "ph,temperature\n7.1,24.0\n7.5,23.4\n"),
        ]);

        $dataset = Dataset::query()->firstOrFail();

        $response = $this->actingAs($user)->delete(route('datasets.destroy', $dataset));

        $response->assertRedirect(route('datasets.index'));
        $response->assertSessionHas('status', 'Dataset eliminado correctamente.');

        $this->assertFalse(Storage::disk('local')->exists($dataset->file_path));
        $this->assertSoftDeleted('datasets', [
            'id' => $dataset->id,
        ]);
    }

    public function test_dataset_downloads_sorted_rows_without_modifying_original_file(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();

        $this->actingAs($user)->post(route('datasets.store'), [
            'name' => 'Dataset ordenado',
            'dataset_file' => UploadedFile::fake()->createWithContent('sorted.csv', "name,value\nbeta,2\nalpha,1\ngamma,3\n"),
        ]);

        $dataset = Dataset::query()->firstOrFail();

        $response = $this->actingAs($user)->get(route('datasets.download', $dataset));

        $response->assertDownload($dataset->original_name);
        $response->assertOk();

        $originalContent = Storage::disk('local')->get($dataset->file_path);

        $this->assertSame("name,value\nbeta,2\nalpha,1\ngamma,3\n", $originalContent);
    }

    public function test_deleting_a_dataset_removes_related_training_configurations_and_jobs(): void
    {
        Storage::fake('local');

        $user = User::factory()->create();
        $dataset = $this->createDataset($user);

        $configuration = TrainingConfiguration::create([
            'dataset_id' => $dataset->id,
            'created_by' => $user->id,
            'target_column' => 'status',
            'algorithm' => 'logistic',
            'parameters' => [],
            'analysis' => [],
        ]);

        $modelPath = 'weka/models/training-job-1.model';
        $logPath = 'weka/logs/training-job-1.log';

        Storage::disk('local')->put($modelPath, 'model');
        Storage::disk('local')->put($logPath, 'log');

        TrainingJob::create([
            'training_configuration_id' => $configuration->id,
            'created_by' => $user->id,
            'dataset_id' => $dataset->id,
            'algorithm' => 'logistic',
            'target_column' => 'status',
            'parameters' => [],
            'status' => 'completed',
            'model_path' => $modelPath,
            'log_path' => $logPath,
            'metrics' => [],
            'confusion_matrix' => [],
            'cross_validation_folds' => 10,
            'random_seed' => 42,
        ]);

        $this->actingAs($user)->delete(route('datasets.destroy', $dataset))
            ->assertRedirect(route('datasets.index'));

        $this->assertSoftDeleted('datasets', ['id' => $dataset->id]);
        $this->assertDatabaseMissing('training_configurations', ['id' => $configuration->id]);
        $this->assertDatabaseMissing('training_jobs', ['training_configuration_id' => $configuration->id]);
        $this->assertFalse(Storage::disk('local')->exists($modelPath));
        $this->assertFalse(Storage::disk('local')->exists($logPath));
    }

    private function createDataset(User $user): Dataset
    {
        $filePath = 'weka/datasets/dataset-cleanup.csv';
        Storage::disk('local')->put($filePath, "ph,temperature,status\n7.2,23.1,normal\n8.0,22.4,alert\n7.5,24.0,normal\n");

        return Dataset::create([
            'name' => 'Dataset cleanup',
            'original_name' => 'dataset-cleanup.csv',
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
}
