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
            'start_time' => ['required', 'string'],
            'end_time' => ['required', 'string'],
            'drying_hours' => ['nullable', 'numeric'],
            'new_weight' => ['required', 'numeric', 'min:0'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'signature_data' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'start_time' => 'waktu jemur',
            'end_time' => 'waktu angkat',
            'drying_hours' => 'lama jemur',
            'new_weight' => 'berat setelah dijemur',
            'end_date' => 'tanggal selesai jemur',
            'pic_name' => 'penanggung jawab',
        ];
    }
}
