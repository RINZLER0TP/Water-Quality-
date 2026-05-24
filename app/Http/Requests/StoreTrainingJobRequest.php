<?php

namespace App\Http\Requests;

use App\Models\TrainingConfiguration;
use App\Models\TrainingJob;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTrainingJobRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'training_configuration_id' => ['required', 'integer', Rule::exists('training_configurations', 'id')->whereNull('deleted_at')],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $configurationId = (int) $this->input('training_configuration_id');
            $configuration = TrainingConfiguration::query()->with('dataset')->find($configurationId);

            if (! $configuration) {
                return;
            }

            if (! $this->user()?->can('create', [TrainingJob::class, $configuration])) {
                $validator->errors()->add('training_configuration_id', 'No tienes permisos para ejecutar esta configuración.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'training_configuration_id.exists' => 'La configuración seleccionada no existe.',
        ];
    }
}
