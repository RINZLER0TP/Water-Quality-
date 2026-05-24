<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePredictionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We'll handle authorization in the controller/policy
    }

    public function rules(): array
    {
        return [
            'training_job_id' => ['required', 'exists:training_jobs,id'],
            'ph' => ['required', 'numeric', 'min:0', 'max:14'],
            'hardness' => ['required', 'numeric', 'min:0'],
            'solids' => ['required', 'numeric', 'min:0'],
            'chloramines' => ['required', 'numeric', 'min:0'],
            'sulfate' => ['required', 'numeric', 'min:0'],
            'conductivity' => ['required', 'numeric', 'min:0'],
            'organic_carbon' => ['required', 'numeric', 'min:0'],
            'trihalomethanes' => ['required', 'numeric', 'min:0'],
            'turbidity' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function getInputData(): array
    {
        return $this->only([
            'ph',
            'hardness',
            'solids',
            'chloramines',
            'sulfate',
            'conductivity',
            'organic_carbon',
            'trihalomethanes',
            'turbidity'
        ]);
    }
}
