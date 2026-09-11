<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductionDailyLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'log_date' => ['required', 'date'],
            'stage' => ['required', 'string'],
            'work_status' => ['required_unless:stage,Selesai', 'nullable', 'string', 'in:selesai,ulang,tertunda'],
            'actual_quantity' => ['required_if:stage,Selesai', 'nullable', 'numeric', 'min:0.01'],
            'pic_name' => ['required', 'string', 'max:255'],
            'notes' => ['required', 'string', 'max:1000'],
            'process_step' => ['nullable', 'string', 'max:100'],
            'wet_result_weight' => ['nullable', 'numeric', 'min:0'],
            'residual_resin_weight' => ['nullable', 'numeric', 'min:0'],
            'residual_resin_material_id' => ['nullable', 'exists:materials,id'],
            'weighed_result_weight' => ['nullable', 'numeric', 'min:0'],
            'vendor_name' => ['nullable', 'string', 'max:100'],
            'vendor_sent_date' => ['nullable', 'date'],
            'vendor_received_date' => ['nullable', 'date'],
            'attachment' => ['nullable', 'file', 'max:10240'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:10240'],
            'signature_data' => ['nullable', 'string'],
            'materials' => ['nullable', 'array'],
            'materials.*.material_id' => ['required_with:materials', 'exists:materials,id'],
            'materials.*.quantity_used' => ['required_with:materials', 'numeric', 'min:0.01'],
        ];
    }

    public function messages(): array
    {
        return [
            'log_date.required' => 'Tanggal laporan harian wajib diisi.',
            'stage.required' => 'Tahap pengerjaan wajib dipilih.',
            'stage.in' => 'Pilihan tahap pengerjaan tidak valid.',
            'work_status.required_unless' => 'Status pengerjaan wajib dipilih.',
            'work_status.in' => 'Pilihan status pengerjaan tidak valid.',
            'actual_quantity.required_if' => 'Jumlah produk jadi hasil produksi wajib diisi saat memilih tahap Selesai.',
            'pic_name.required' => 'Nama petugas pelapor (PIC) wajib diisi.',
            'notes.required' => 'Catatan proses harian wajib diisi.',
            'attachment.max' => 'Ukuran berkas lampiran maksimal 10 MB.',
            'attachments.*.max' => 'Ukuran setiap berkas lampiran maksimal 10 MB.',
        ];
    }
}
