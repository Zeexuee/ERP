<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class RecountMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_stock' => ['required', 'numeric', 'min:0'],
            'weighed_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }
}
