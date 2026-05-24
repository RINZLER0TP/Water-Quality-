<?php

namespace Tests\Feature;

use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrainingConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_preview_a_dataset_for_training_configuration(): void
    {
        $user = User::factory()->create();
        $dataset = $this->createDataset($user);

        $response = $this->actingAs($user)->get(route('training-configurations.preview', $dataset));

        $response->assertOk();
        $response->assertJsonPath('statistics.rows_count', 3);
        $response->assertJsonPath('statistics.columns_count', 3);
        $response->assertJsonPath('statistics.missing_values', 0);
        $response->assertJsonPath('statistics.numeric_columns', 2);
        $response->assertJsonPath('suggested_target.name', 'status');
        $response->assertJsonPath('compatibility.is_compatible', true);
    }

    public function test_user_can_store_a_training_configuration_with_snapshot(): void
    {
        $user = User::factory()->create();
        $dataset = $this->createDataset($user);

        $response = $this->actingAs($user)->post(route('training-configurations.store'), [
            'dataset_id' => $dataset->id,
            'target_column' => 'status',
            'algorithm' => 'logistic',
            'parameters' => [
                'ridge' => 0.0001,
                'max_iterations' => 250,
            ],
        ]);

        $configuration = TrainingConfiguration::query()->first();

        $response->assertRedirect(route('training-configurations.show', $configuration));
        $response->assertSessionHas('status', 'Configuración de entrenamiento creada correctamente.');

        $this->assertNotNull($configuration);
        $this->assertSame($dataset->id, $configuration->dataset_id);
        $this->assertSame($user->id, $configuration->created_by);
        $this->assertSame('status', $configuration->target_column);
        $this->assertSame('logistic', $configuration->algorithm->value);
        $this->assertSame(3, $configuration->analysis['statistics']['rows_count']);
        $this->assertSame('status', $configuration->analysis['suggested_target']['name']);
    }

    public function test_deleted_dataset_is_not_shown_in_training_configuration_index(): void
    {
        $user = User::factory()->create();
        $dataset = $this->createDataset($user);

        $this->actingAs($user)->post(route('training-configurations.store'), [
            'dataset_id' => $dataset->id,
            'target_column' => 'status',
            'algorithm' => 'zeror',
            'parameters' => [],
        ])->assertRedirect();

        $dataset->delete();

        $response = $this->actingAs($user)->get(route('training-configurations.index'));

        $response->assertOk();
        $response->assertDontSee('Dataset eliminado');
        $response->assertDontSee('Dataset training');
    }

    private function createDataset(User $user): Dataset
    {
        Storage::fake('local');

        $this->actingAs($user)->post(route('datasets.store'), [
            'name' => 'Dataset training',
            'dataset_file' => UploadedFile::fake()->createWithContent('training.csv', "ph,temperature,status\n7.2,23.1,normal\n8.0,22.4,alert\n7.5,24.0,normal\n"),
        ]);

        return Dataset::query()->firstOrFail();
    }
}