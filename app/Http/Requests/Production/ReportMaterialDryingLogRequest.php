<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ReportMaterialDryingLogRequest extends FormRequest
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
            'end_date' => ['required', 'date'],
            'new_weight' => ['required', 'numeric', 'min:0'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'signature_data' => ['nullable', 'string'],
        ];
    }
}
