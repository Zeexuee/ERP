<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
        $isCompleted = $this->boolean('is_completed');
        $destinationType = $this->string('destination_type')->toString();

        return [
            'weighed_date' => ['required', 'date', 'before_or_equal:today'],
            'new_weight' => ['required', 'numeric', 'min:0.01'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_completed' => ['required', 'boolean'],
            'destination_type' => [
                'nullable',
                Rule::requiredIf($isCompleted),
                Rule::in(['existing', 'new']),
            ],
            'output_material_id' => [
                'nullable',
                Rule::requiredIf($isCompleted && $destinationType === 'existing'),
                'integer',
                'exists:materials,id',
            ],
            'new_output_name' => [
                'nullable',
                Rule::requiredIf($isCompleted && $destinationType === 'new'),
                'string',
                'max:255',
            ],
            'new_output_code' => ['nullable', 'string', 'max:50', 'unique:materials,code'],
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
            'destination_type.required' => 'Pilih barang baku tujuan hasil jemur.',
            'output_material_id.required' => 'Pilih barang baku gudang tujuan.',
            'output_material_id.exists' => 'Barang baku gudang tujuan tidak valid.',
            'new_output_name.required' => 'Nama barang baku baru wajib diisi.',
            'new_output_code.unique' => 'Kode barang baku sudah digunakan.',
        ];
    }
}
