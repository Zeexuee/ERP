<?php

namespace App\Http\Requests\Invoice;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sales_order_id' => ['required', 'exists:sales_orders,id'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
