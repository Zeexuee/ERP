<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['nullable', 'string', 'max:50', 'unique:materials,code'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:100'],
            'unit' => ['nullable', 'string', 'max:50'],
            'stock_quantity' => ['nullable', 'numeric', 'min:0'],
            'minimum_stock' => ['nullable', 'numeric', 'min:0'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
