<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreColoringReportRequest extends FormRequest
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
            'result_weight' => ['nullable', 'numeric', 'min:0'],
            'loss_weight' => ['nullable', 'numeric', 'min:0'],
            'report_date' => ['required', 'date'],
            'report_pic_name' => ['required', 'string', 'max:255'],
            'report_notes' => ['nullable', 'string'],
            'report_signature_data' => ['required', 'string'],
            'report_image' => ['nullable', 'image', 'max:2048'],
            'continuation_action' => ['required', 'in:finish,hold,continue_celup,continue_warna'],

            // Multiple support materials for next process
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required_with:materials', 'exists:materials,id'],
            'materials.*.weight' => ['required_with:materials', 'numeric', 'min:0'],
        ];
    }
}
