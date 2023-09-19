<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMechanicRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            "name" => ['required', 'string', Rule::unique('mechanics', 'name')->ignore($this->mechanic?->id)],
            'is_active' => ['boolean'],
        ];
    }
}
