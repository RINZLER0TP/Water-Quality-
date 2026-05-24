<?php

namespace App\Http\Requests;

use App\Enums\TrainingAlgorithm;
use App\Models\Dataset;
use App\Models\TrainingConfiguration;
use App\Services\TrainingConfigurations\TrainingConfigurationService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use RuntimeException;

class StoreTrainingConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'target_column' => ['required', 'string', 'max:150'],
            'algorithm' => ['required', 'string', Rule::in(TrainingAlgorithm::values())],
            'parameters' => ['nullable', 'array'],
            'parameters.bucket_size' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'parameters.use_kernel_estimator' => ['nullable', 'boolean'],
            'parameters.use_supervised_discretization' => ['nullable', 'boolean'],
            'parameters.ridge' => ['nullable', 'numeric', 'min:0'],
            'parameters.max_iterations' => ['nullable', 'integer', 'min:1', 'max:10000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $datasetId = (int) $this->input('dataset_id');
            $dataset = Dataset::query()->withoutTrashed()->find($datasetId);

            if (! $dataset) {
                return;
            }

            if (! $this->user()?->can('create', [TrainingConfiguration::class, $dataset])) {
                $validator->errors()->add('dataset_id', 'No tienes permisos para configurar este dataset.');

                return;
            }

            try {
                $analysis = app(TrainingConfigurationService::class)->previewDataset($dataset, 10);
            } catch (RuntimeException $exception) {
                $validator->errors()->add('dataset_id', $exception->getMessage());

                return;
            }

            $targetColumn = (string) $this->input('target_column');
            $column = collect($analysis['columns'] ?? [])->firstWhere('name', $targetColumn);

            if (! $column) {
                $validator->errors()->add('target_column', 'La columna objetivo no existe en el dataset.');

                return;
            }

            if (! in_array($column['type'] ?? null, ['categorical', 'boolean'], true)) {
                $validator->errors()->add('target_column', 'La columna objetivo debe ser categórica o booleana para estos algoritmos.');
            }

            $issues = $analysis['compatibility']['issues'] ?? [];

            if (! empty($issues) && empty($analysis['compatibility']['is_compatible'])) {
                $validator->errors()->add('dataset_id', 'El dataset no es compatible con la configuración de entrenamiento elegida.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'dataset_id.exists' => 'El dataset seleccionado no existe.',
            'algorithm.in' => 'Selecciona un algoritmo compatible.',
            'parameters.bucket_size.integer' => 'El tamaño mínimo de bucket debe ser numérico.',
            'parameters.ridge.numeric' => 'El valor de ridge debe ser numérico.',
        ];
    }
}