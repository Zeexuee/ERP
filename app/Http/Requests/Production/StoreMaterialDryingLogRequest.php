<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialDryingLogRequest extends FormRequest
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
            'material_id' => ['required', 'exists:materials,id'],
            'start_date' => ['required', 'date'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'signature_data' => ['nullable', 'string'],
        ];
    }
}
