<?php

namespace App\Http\Requests\Production;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMaterialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $material = $this->route('material');
        $materialId = $material ? $material->id : null;

        return [
            'code' => ['required', 'string', 'max:100', 'unique:materials,code,'.$materialId],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Kode bahan baku wajib diisi.',
            'code.unique' => 'Kode bahan baku tersebut sudah digunakan.',
            'name.required' => 'Nama bahan baku wajib diisi.',
            'category.required' => 'Kategori bahan baku wajib diisi.',
            'minimum_stock.required' => 'Minimal alert stok wajib diisi.',
        ];
    }
}
