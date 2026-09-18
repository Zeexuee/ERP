<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreTembakBatchRequest extends FormRequest
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
            'wood_material_id' => ['required', 'exists:materials,id'],
            'wood_weight' => ['required', 'numeric', 'min:0.01'],
            'resin_material_id' => ['required', 'exists:materials,id'],
            'resin_weight' => ['required', 'numeric', 'min:0.01'],
            'tembak_date' => ['required', 'date'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string'],
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
            'wood_material_id.required' => 'Pilih bahan kayu yang akan diproses tembak.',
            'wood_material_id.exists' => 'Bahan kayu yang dipilih tidak valid.',
            'wood_weight.required' => 'Berat kayu wajib diisi.',
            'wood_weight.min' => 'Berat kayu minimal 0.01.',
            'resin_material_id.required' => 'Pilih minyak atau resin yang digunakan.',
            'resin_material_id.exists' => 'Bahan minyak/resin yang dipilih tidak valid.',
            'resin_weight.required' => 'Berat/kuantitas resin yang digunakan wajib diisi.',
            'resin_weight.min' => 'Kuantitas resin minimal 0.01.',
            'tembak_date.required' => 'Tanggal proses tembak wajib diisi.',
            'pic_name.required' => 'Nama penanggung jawab (PIC) wajib diisi.',
        ];
    }
}
