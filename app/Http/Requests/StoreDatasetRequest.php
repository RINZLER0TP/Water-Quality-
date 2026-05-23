<?php

namespace App\Http\Requests;

use App\Models\Dataset;
use Illuminate\Foundation\Http\FormRequest;

class StoreDatasetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', Dataset::class) ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:150'],
            'dataset_file' => ['required', 'file', 'mimes:csv,txt', 'max:51200'],
        ];
    }

    public function messages(): array
    {
        return [
            'dataset_file.mimes' => 'El archivo debe ser CSV.',
            'dataset_file.max' => 'El archivo no debe superar 50 MB.',
        ];
    }
}
