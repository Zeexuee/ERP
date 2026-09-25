<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreFinishingSessionRequest extends FormRequest
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
            'source_material_id' => ['required', 'integer', 'exists:materials,id'],
            'initial_weight' => ['required', 'numeric', 'min:0.01'],
            'finishing_date' => ['required', 'date', 'before_or_equal:today'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string', 'starts_with:data:image/png;base64,', 'max:1500000'],
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
            'source_material_id.required' => 'Pilih bahan hasil tembak yang akan difinishing.',
            'source_material_id.exists' => 'Bahan yang dipilih tidak valid.',
            'initial_weight.required' => 'Berat bahan yang masuk finishing wajib diisi.',
            'initial_weight.min' => 'Berat bahan minimal 0,01 kg.',
            'finishing_date.required' => 'Tanggal mulai finishing wajib diisi.',
            'finishing_date.before_or_equal' => 'Tanggal mulai finishing tidak boleh melewati hari ini.',
            'pic_name.required' => 'Nama penanggung jawab (PIC) wajib diisi.',
            'signature_data.starts_with' => 'Format tanda tangan tidak valid.',
            'signature_data.max' => 'Ukuran tanda tangan terlalu besar.',
        ];
    }
}
