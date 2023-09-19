<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Sabberworm\CSS\Rule\Rule;

class PayToBillRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'price' => ['required','numeric','gt:0', function ($attr, $val, $fail) {
                if ($this->bill->is_payed) {
                    $fail(__('Bill is already Payed'));
                }
            }],
            'notes' => 'nullable|string',
        ];
    }
}
