<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexTrainingConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'min:6', 'max:48'],
        ];
    }
}