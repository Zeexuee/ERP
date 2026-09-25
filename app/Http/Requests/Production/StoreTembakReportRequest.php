<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTembakReportRequest extends FormRequest
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
        $hasResidualResin = $this->float('residual_resin_weight') > 0;
        $residualDestinationType = $this->string('residual_destination_type')->toString();

        return [
            'report_date' => ['required', 'date', 'before_or_equal:today'],
            'report_pic_name' => ['required', 'string', 'max:100'],
            'wet_result_weight' => ['required', 'numeric', 'min:0.01'],
            'residual_resin_weight' => ['nullable', 'numeric', 'min:0'],
            'residual_destination_type' => ['required', Rule::in(['none', 'existing', 'new'])],
            'existing_residual_material_id' => [
                'nullable',
                Rule::requiredIf($hasResidualResin && $residualDestinationType === 'existing'),
                'integer',
                'exists:materials,id',
            ],
            'new_residual_name' => [
                'nullable',
                Rule::requiredIf($hasResidualResin && $residualDestinationType === 'new'),
                'string',
                'max:255',
            ],
            'new_residual_code' => ['nullable', 'string', 'max:50', 'unique:materials,code'],
            'report_notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['required', 'string', 'starts_with:data:image/png;base64,', 'max:1500000'],
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
            'report_date.required' => 'Tanggal laporan wajib diisi.',
            'report_date.before_or_equal' => 'Tanggal laporan tidak boleh melewati hari ini.',
            'report_pic_name.required' => 'Nama PIC penimbang atau pelapor wajib diisi.',
            'wet_result_weight.required' => 'Berat hasil tembak wajib diisi.',
            'wet_result_weight.min' => 'Berat hasil tembak minimal 0,01 kg.',
            'residual_resin_weight.min' => 'Berat getah sisa tidak boleh negatif.',
            'residual_destination_type.required' => 'Pilih perlakuan untuk getah sisa.',
            'existing_residual_material_id.required' => 'Pilih barang Getah tujuan di gudang.',
            'existing_residual_material_id.exists' => 'Barang Getah tujuan tidak valid.',
            'new_residual_name.required' => 'Nama barang Getah baru wajib diisi.',
            'new_residual_code.unique' => 'Kode barang Getah sudah digunakan.',
            'signature_data.required' => 'Tanda tangan pelapor wajib diisi.',
            'signature_data.starts_with' => 'Format tanda tangan pelapor tidak valid.',
            'signature_data.max' => 'Ukuran tanda tangan pelapor terlalu besar.',
        ];
    }
}
