<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreColoringBatchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'process_type' => ['required', 'string', 'in:celup,warna'],
            'coloring_date' => ['required', 'date'],
            'pic_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'material_branch_id' => ['required', 'exists:material_branches,id'],
            'signature_data' => ['required', 'string'],

            // Multiple support materials
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.weight' => ['required', 'numeric', 'min:0'],
        ];
    }
}
