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
            'woods' => ['required', 'array', 'min:1'],
            'woods.*.material_id' => ['required', 'exists:materials,id'],
            'woods.*.weight' => ['required', 'numeric', 'min:0.01'],
            'resins' => ['required', 'array', 'min:1'],
            'resins.*.material_id' => ['required', 'exists:materials,id'],
            'resins.*.weight' => ['required', 'numeric', 'min:0.01'],
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
            'woods.required' => 'Bahan kayu wajib diisi.',
            'woods.min' => 'Pilih minimal satu bahan kayu.',
            'woods.*.material_id.required' => 'Pilih bahan kayu yang akan diproses tembak.',
            'woods.*.material_id.exists' => 'Bahan kayu yang dipilih tidak valid.',
            'woods.*.weight.required' => 'Berat kayu wajib diisi.',
            'woods.*.weight.min' => 'Berat kayu minimal 0.01.',
            'resins.required' => 'Minyak atau resin wajib diisi.',
            'resins.min' => 'Pilih minimal satu minyak atau resin.',
            'resins.*.material_id.required' => 'Pilih minyak atau resin yang digunakan.',
            'resins.*.material_id.exists' => 'Bahan minyak/resin yang dipilih tidak valid.',
            'resins.*.weight.required' => 'Kuantitas resin wajib diisi.',
            'resins.*.weight.min' => 'Kuantitas resin minimal 0.01.',
            'tembak_date.required' => 'Tanggal proses tembak wajib diisi.',
            'pic_name.required' => 'Nama penanggung jawab (PIC) wajib diisi.',
        ];
    }
}
