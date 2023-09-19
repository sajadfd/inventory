<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SetOptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'option_name' => 'required|string',
            'option_value' => 'required',
        ];
    }
}
