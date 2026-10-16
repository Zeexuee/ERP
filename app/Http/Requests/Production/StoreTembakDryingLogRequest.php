<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTembakDryingLogRequest extends FormRequest
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
            'weighed_date' => ['required', 'date', 'before_or_equal:today'],
            'new_weight' => ['required', 'numeric', 'min:0.01'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_completed' => ['required', 'boolean'],
        ];
    }

    /**
     * Custom messages for validation errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'weighed_date.required' => 'Tanggal timbang jemur wajib diisi.',
            'weighed_date.before_or_equal' => 'Tanggal timbang jemur tidak boleh melewati hari ini.',
            'new_weight.required' => 'Berat setelah jemur wajib diisi.',
            'new_weight.min' => 'Berat setelah jemur minimal 0,01 kg.',
            'pic_name.required' => 'Nama PIC timbang jemur wajib diisi.',
        ];
    }
}
