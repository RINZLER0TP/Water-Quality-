<?php

namespace App\Http\Requests;

use App\Services\Datasets\CsvDatasetInspector;
use Illuminate\Foundation\Http\FormRequest;

class StoreDatasetPredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'training_job_id' => ['required', 'exists:training_jobs,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'training_job_id.required' => 'Debes seleccionar un modelo entrenado.',
            'training_job_id.exists' => 'El modelo seleccionado no existe.',
        ];
    }
}