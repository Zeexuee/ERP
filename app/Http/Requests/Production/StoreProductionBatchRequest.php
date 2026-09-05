<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'batch_number' => ['nullable', 'string', 'max:100', 'unique:production_batches,batch_number'],
            'product_id' => ['required', 'exists:products,id'],
            'production_request_id' => ['nullable', 'exists:production_requests,id'],
            'target_quantity' => ['required', 'numeric', 'min:0.01'],
            'start_date' => ['required', 'date'],
            'target_completion_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'pic_name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.material_id' => ['required', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'batch_number.unique' => 'Nomor batch produksi tersebut sudah digunakan. Silakan gunakan nomor lain.',
            'product_id.required' => 'Target produk jadi wajib dipilih.',
            'target_quantity.required' => 'Target kuantitas hasil jadi wajib diisi.',
            'target_quantity.min' => 'Target kuantitas minimal 0.01.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'pic_name.required' => 'Nama PIC wajib diisi.',
            'materials.required' => 'Setidaknya masukkan minimal satu bahan baku yang digunakan untuk produksi.',
            'materials.min' => 'Setidaknya masukkan minimal satu bahan baku yang digunakan untuk produksi.',
            'materials.*.material_id.required' => 'Bahan baku wajib dipilih.',
            'materials.*.quantity_used.required' => 'Jumlah penggunaan bahan wajib diisi.',
            'materials.*.quantity_used.min' => 'Jumlah penggunaan bahan minimal 0.01.',
        ];
    }
}
