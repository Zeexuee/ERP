<?php

namespace App\Http\Requests\Production;

use App\Enums\FinishingProcessType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFinishingStepRequest extends FormRequest
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
        $hasWaste = $this->float('waste_weight') > 0;
        $wasteDestinationType = $this->string('waste_destination_type')->toString();

        return [
            'process_type' => ['required', Rule::in(FinishingProcessType::values())],
            'step_date' => ['required', 'date', 'before_or_equal:today'],
            'weight_after' => ['required', 'numeric', 'min:0.01'],
            'waste_weight' => ['nullable', 'numeric', 'min:0'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['nullable', 'string', 'starts_with:data:image/png;base64,', 'max:1500000'],

            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['nullable', 'integer', 'exists:materials,id'],
            'materials.*.quantity' => ['nullable', 'numeric', 'min:0.01', 'required_with:materials.*.material_id'],

            'waste_destination_type' => ['required', Rule::in(['none', 'existing', 'new'])],
            'waste_material_id' => [
                'nullable',
                Rule::requiredIf($hasWaste && $wasteDestinationType === 'existing'),
                'integer',
                'exists:materials,id',
            ],
            'new_waste_name' => [
                'nullable',
                Rule::requiredIf($hasWaste && $wasteDestinationType === 'new'),
                'string',
                'max:255',
            ],
            'new_waste_code' => ['nullable', 'string', 'max:50', 'unique:materials,code'],
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
            'process_type.required' => 'Pilih jenis proses finishing.',
            'process_type.in' => 'Jenis proses finishing tidak valid.',
            'step_date.required' => 'Tanggal proses wajib diisi.',
            'step_date.before_or_equal' => 'Tanggal proses tidak boleh melewati hari ini.',
            'weight_after.required' => 'Berat setelah proses wajib diisi.',
            'weight_after.min' => 'Berat setelah proses minimal 0,01 kg.',
            'waste_weight.min' => 'Berat ampas buangan tidak boleh negatif.',
            'pic_name.required' => 'Nama PIC proses wajib diisi.',
            'materials.*.quantity.required_with' => 'Jumlah bahan yang dipakai wajib diisi.',
            'materials.*.quantity.min' => 'Jumlah bahan yang dipakai minimal 0,01.',
            'materials.*.material_id.exists' => 'Bahan yang dipilih tidak valid.',
            'waste_destination_type.required' => 'Pilih perlakuan untuk ampas buangan.',
            'waste_material_id.required' => 'Pilih barang gudang tujuan ampas.',
            'waste_material_id.exists' => 'Barang gudang tujuan ampas tidak valid.',
            'new_waste_name.required' => 'Nama barang ampas baru wajib diisi.',
            'new_waste_code.unique' => 'Kode barang ampas sudah digunakan.',
            'signature_data.starts_with' => 'Format tanda tangan tidak valid.',
        ];
    }
}
