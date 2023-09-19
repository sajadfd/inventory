<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bill_id' => ['required', Rule::exists('bills', 'id')->where('is_payed', false)],
            'price' => 'required|numeric|gt:0',
            'notes' => 'nullable|string'
        ];
    }
}
