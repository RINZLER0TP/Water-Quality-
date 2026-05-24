<?php

namespace App\Http\Requests;

use App\Models\Dataset;
use App\Services\Datasets\CsvDatasetInspector;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use RuntimeException;

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
            'dataset_file' => ['required', 'file', 'max:51200', 'extensions:csv', 'mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/octet-stream'],
        ];
    }

    public function messages(): array
    {
        return [
            'dataset_file.extensions' => 'El archivo debe tener extensión .csv.',
            'dataset_file.mimetypes' => 'El archivo debe ser un CSV válido.',
            'dataset_file.max' => 'El archivo no debe superar 50 MB.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $file = $this->file('dataset_file');

            if (! $file || ! $file->isValid()) {
                return;
            }

            try {
                app(CsvDatasetInspector::class)->inspect($file);
            } catch (RuntimeException $exception) {
                $validator->errors()->add('dataset_file', $exception->getMessage());
            }
        });
    }
}
