<?php

namespace Modules\HR\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InstallmentRequest extends FormRequest
{


    public function rules()
    {
        switch ($this->getMethod()) {
            case 'PUT': // Update
                return [
                    "notes" => ['nullable', 'string'],
                ];

            default: // Other methods
                return [];
        }
    }


    public function authorize()
    {
        return true;
    }
}
