<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWaterSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', \App\Models\WaterSample::class) ?? true;
    }

    public function rules(): array
    {
        return [
            'ph' => ['required', 'numeric', 'between:0,14'],
            'temperature' => ['required', 'numeric'],
            'status' => ['required', 'string', 'max:50'],
            'collected_at' => ['nullable', 'date'],
        ];
    }
}
