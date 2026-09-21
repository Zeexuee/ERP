<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SortMaterialRequest extends FormRequest
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
        $material = $this->route('material');
        $maxStock = $material ? (float) $material->stock_quantity : 9999999;

        return [
            'sorted_quantity' => ['required', 'numeric', 'min:0.01', "max:{$maxStock}"],
            'destination_type' => ['required', 'in:new,existing'],
            'new_name' => ['required_if:destination_type,new', 'nullable', 'string', 'max:255'],
            'new_code' => ['nullable', 'string', 'max:100'],
            'new_category' => ['nullable', 'string', 'max:100'],
            'existing_material_id' => ['required_if:destination_type,existing', 'nullable', 'exists:materials,id'],
            'actor_by' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'signature_data' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'sorted_quantity.required' => 'Kuantitas split wajib diisi.',
            'sorted_quantity.min' => 'Kuantitas split harus lebih besar dari 0.',
            'sorted_quantity.max' => 'Kuantitas split tidak boleh melebihi stok yang tersedia.',
            'destination_type.required' => 'Pilih tujuan split (Bahan Baru / Gabung Bahan Ada).',
            'destination_type.in' => 'Tujuan split tidak valid.',
            'new_name.required_if' => 'Nama bahan baku baru wajib diisi.',
            'existing_material_id.required_if' => 'Pilih bahan baku yang akan digabungkan.',
            'existing_material_id.exists' => 'Bahan baku tujuan yang dipilih tidak ditemukan.',
            'actor_by.required' => 'Nama petugas / penanggung jawab wajib diisi.',
        ];
    }
}
