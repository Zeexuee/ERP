<?php

namespace App\Http\Requests\Production;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompleteFinishingSessionRequest extends FormRequest
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
        $destinationType = $this->string('product_destination_type')->toString();

        return [
            'completed_date' => ['required', 'date', 'before_or_equal:today'],
            'completion_pic_name' => ['required', 'string', 'max:100'],
            'output_quantity' => ['required', 'integer', 'min:1'],
            'completion_notes' => ['nullable', 'string', 'max:1000'],
            'signature_data' => ['required', 'string', 'starts_with:data:image/png;base64,', 'max:1500000'],

            'product_destination_type' => ['required', Rule::in(['existing', 'new'])],
            'product_id' => [
                'nullable',
                Rule::requiredIf($destinationType === 'existing'),
                'integer',
                'exists:products,id',
            ],
            'new_product_name' => [
                'nullable',
                Rule::requiredIf($destinationType === 'new'),
                'string',
                'max:255',
            ],
            'new_product_sku' => ['nullable', 'string', 'max:100', 'unique:products,sku'],
            'new_product_price' => [
                'nullable',
                Rule::requiredIf($destinationType === 'new'),
                'numeric',
                'min:0',
            ],
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
            'completed_date.required' => 'Tanggal selesai finishing wajib diisi.',
            'completed_date.before_or_equal' => 'Tanggal selesai tidak boleh melewati hari ini.',
            'completion_pic_name.required' => 'Nama PIC penyelesaian wajib diisi.',
            'output_quantity.required' => 'Jumlah barang jadi wajib diisi.',
            'output_quantity.min' => 'Jumlah barang jadi minimal 1.',
            'product_destination_type.required' => 'Pilih barang jadi tujuan hasil finishing.',
            'product_id.required' => 'Pilih barang jadi yang sudah ada di katalog.',
            'product_id.exists' => 'Barang jadi tujuan tidak valid.',
            'new_product_name.required' => 'Nama barang jadi baru wajib diisi.',
            'new_product_sku.unique' => 'SKU barang jadi sudah digunakan.',
            'new_product_price.required' => 'Harga jual barang jadi baru wajib diisi.',
            'signature_data.required' => 'Tanda tangan penyelesaian wajib diisi.',
            'signature_data.starts_with' => 'Format tanda tangan tidak valid.',
            'signature_data.max' => 'Ukuran tanda tangan terlalu besar.',
        ];
    }
}
