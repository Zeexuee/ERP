<?php

namespace App\Http\Requests\Production;

use App\Models\Material;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMaterialDryingLogRequest extends FormRequest
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
            'material_id' => [
                'required',
                'exists:materials,id',
                function ($attribute, $value, $fail) {
                    $material = Material::find($value);
                    if (! $material) {
                        return;
                    }
                    if ($material->category !== 'KCB') {
                        $fail('Hanya bahan baku berkategori KCB yang dapat dijemur.');
                    } elseif ($material->stock_quantity <= 0) {
                        $fail('Bahan baku ini tidak memiliki stok yang tersedia untuk dijemur.');
                    } elseif ($material->isInActiveProcess()) {
                        $processName = $material->getActiveProcessName() ?? 'lain';
                        $fail("Bahan baku ini sedang dalam proses {$processName} dan belum selesai.");
                    }
                },
            ],
            'start_date' => ['required', 'date'],
            'start_time' => ['nullable', 'string'],
            'pic_name' => ['required', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'signature_data' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'material_id.exists' => 'Bahan baku yang dipilih harus berkategori KCB.',
        ];
    }
}
