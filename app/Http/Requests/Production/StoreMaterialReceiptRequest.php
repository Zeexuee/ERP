<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'material_id' => ['nullable', 'exists:materials,id'],
            'material_name' => ['required', 'string', 'max:255'],
            'unit' => ['nullable', 'string', 'max:50'],
            'category' => ['nullable', 'string', 'max:100'],
            'ordered_quantity' => ['nullable', 'numeric', 'min:0'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'source_or_supplier' => ['required', 'string', 'max:255'],
            'received_date' => ['required', 'date'],
            'received_by' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
        ];
    }
}
