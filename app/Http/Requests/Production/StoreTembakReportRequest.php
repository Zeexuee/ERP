<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'report_date' => ['required', 'date'],
            'report_pic_name' => ['required', 'string', 'max:100'],
            'wet_result_weight' => ['required', 'numeric', 'min:0.01'],
            'residual_resin_weight' => ['nullable', 'numeric', 'min:0'],
            'residual_resin_material_id' => ['nullable', 'exists:materials,id'],
            'dried_result_weight' => ['required', 'numeric', 'min:0.01'],
            'output_material_id' => ['required', 'exists:materials,id'],
            'report_notes' => ['nullable', 'string', 'max:1000'],
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
            'report_date.required' => 'Tanggal laporan wajib diisi.',
            'report_pic_name.required' => 'Nama PIC penimbang/pelapor wajib diisi.',
            'wet_result_weight.required' => 'Hasil timbang basah setelah tembak wajib diisi.',
            'wet_result_weight.min' => 'Hasil timbang basah minimal 0.01.',
            'dried_result_weight.required' => 'Hasil timbang kering setelah jemur wajib diisi.',
            'dried_result_weight.min' => 'Hasil timbang kering minimal 0.01.',
            'output_material_id.required' => 'Pilih bahan hasil tembak yang akan ditambahkan ke stok gudang.',
            'output_material_id.exists' => 'Bahan hasil tembak yang dipilih tidak valid.',
        ];
    }
}
