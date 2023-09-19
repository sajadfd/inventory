<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePaymentRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'notes'=>'nullable|string',
        ];
    }
}
