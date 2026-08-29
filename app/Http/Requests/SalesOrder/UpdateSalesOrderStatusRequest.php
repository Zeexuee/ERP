<?php

namespace App\Http\Requests\SalesOrder;

use App\Enums\SalesOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateSalesOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(SalesOrderStatus::class)],
        ];
    }
}
